<?php

namespace App\Services\Notifications;

use App\Models\Ticket;
use App\Models\UserPushToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    public function sendTicketCreated(Ticket $ticket): void
    {
        $this->sendToAssignedEngineers(
            ticket: $ticket,
            title: 'Ticket baru masuk',
            body: trim(($ticket->ticket_number ?? '-') . ' - ' . ($ticket->title ?? 'Ticket baru')),
            data: [
                'type' => 'ticket_created',
                'ticket_id' => (string) $ticket->id,
                'ticket_number' => (string) ($ticket->ticket_number ?? ''),
                'screen' => 'task_detail',
            ],
        );
    }

    public function sendTicketAssigned(Ticket $ticket): void
    {
        $this->sendToAssignedEngineers(
            ticket: $ticket,
            title: 'Ticket baru ditugaskan',
            body: trim(($ticket->ticket_number ?? '-') . ' - ' . ($ticket->title ?? 'Ticket baru ditugaskan')),
            data: [
                'type' => 'ticket_assigned',
                'ticket_id' => (string) $ticket->id,
                'ticket_number' => (string) ($ticket->ticket_number ?? ''),
                'screen' => 'task_detail',
            ],
        );
    }

    public function sendTicketCompleted(Ticket $ticket): void
    {
        $this->sendToAssignedEngineers(
            ticket: $ticket,
            title: 'Ticket selesai',
            body: trim(($ticket->ticket_number ?? '-') . ' - ' . ($ticket->title ?? 'Ticket selesai')),
            data: [
                'type' => 'ticket_completed',
                'ticket_id' => (string) $ticket->id,
                'ticket_number' => (string) ($ticket->ticket_number ?? ''),
                'screen' => 'task_detail',
            ],
        );
    }

    private function sendToAssignedEngineers(Ticket $ticket, string $title, string $body, array $data = []): void
    {
        $engineerIds = $ticket->assignedEngineers
            ->pluck('id')
            ->when($ticket->assigned_engineer_id !== null, fn ($ids) => $ids->push($ticket->assigned_engineer_id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($engineerIds->isEmpty()) {
            return;
        }

        $this->sendToUserIds($engineerIds->all(), $title, $body, $data);
    }

    private function sendToUserIds(array $userIds, string $title, string $body, array $data = []): void
    {
        if (! (bool) config('firebase.enabled')) {
            return;
        }

        $projectId = (string) config('firebase.project_id');
        if ($projectId === '') {
            Log::warning('Firebase push skipped: FIREBASE_PROJECT_ID is empty.');
            return;
        }

        $tokens = UserPushToken::query()
            ->where('is_active', true)
            ->whereIn('user_id', $userIds)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        $accessToken = $this->accessToken();
        if ($accessToken === null) {
            return;
        }

        $tokens->chunk(100)->each(function ($chunk) use ($projectId, $accessToken, $title, $body, $data): void {
            foreach ($chunk as $deviceToken) {
                $this->sendMessage($projectId, $accessToken, (string) $deviceToken, $title, $body, $data);
            }
        });
    }

    private function sendMessage(string $projectId, string $accessToken, string $deviceToken, string $title, string $body, array $data): void
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $deviceToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $this->stringData($data),
                        'android' => [
                            'notification' => [
                                'channel_id' => config('firebase.android_channel_id', 'engineering_ops_channel'),
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return;
            }

            $this->handleFailedToken($deviceToken, $response->json());
            Log::warning('Firebase push failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Firebase push exception.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function accessToken(): ?string
    {
        return Cache::remember('firebase_access_token', 3300, function (): ?string {
            $credentials = $this->credentials();
            if ($credentials === null) {
                return null;
            }

            $now = time();
            $assertion = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ], JSON_THROW_ON_ERROR)) . '.' . $this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));

            $signature = '';
            $signed = openssl_sign($assertion, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            if (! $signed) {
                Log::warning('Firebase push skipped: unable to sign service account JWT.');
                return null;
            }

            $jwt = $assertion . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

            if (! $response->successful()) {
                Log::warning('Firebase push skipped: unable to fetch access token.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return (string) $response->json('access_token');
        });
    }

    private function credentials(): ?array
    {
        $path = (string) config('firebase.credentials_path');
        if ($path === '') {
            Log::warning('Firebase push skipped: FIREBASE_CREDENTIALS_PATH is empty.');
            return null;
        }

        $resolvedPath = str_starts_with($path, '/')
            ? $path
            : base_path($path);

        if (! is_file($resolvedPath)) {
            Log::warning('Firebase push skipped: credentials file not found.', [
                'path' => $resolvedPath,
            ]);
            return null;
        }

        $credentials = json_decode((string) file_get_contents($resolvedPath), true);
        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            Log::warning('Firebase push skipped: invalid service account credentials.');
            return null;
        }

        return $credentials;
    }

    private function handleFailedToken(string $deviceToken, ?array $payload): void
    {
        $errorCode = data_get($payload, 'error.details.0.errorCode')
            ?? data_get($payload, 'error.status');

        if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
            UserPushToken::query()
                ->where('token', $deviceToken)
                ->update(['is_active' => false]);
        }
    }

    private function stringData(array $data): array
    {
        return collect($data)
            ->mapWithKeys(fn ($value, $key) => [(string) $key => (string) $value])
            ->all();
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
