<?php

namespace App\Modules\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\UserPushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MobileNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = max(5, min((int) $request->input('limit', 25), 100));

        $notifications = $this->ticketNotifications($user->id, $limit)
            ->merge($this->inspectionNotifications($user->id, $limit))
            ->sortByDesc('occurred_at')
            ->take($limit)
            ->values();

        return response()->json([
            'data' => $notifications->all(),
            'meta' => [
                'total' => $notifications->count(),
                'unread_count' => $notifications->where('is_unread', true)->count(),
            ],
        ]);
    }

    public function storeDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        $token = UserPushToken::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Device token registered.',
            'data' => [
                'id' => $token->id,
                'platform' => $token->platform,
                'device_name' => $token->device_name,
                'app_version' => $token->app_version,
                'last_used_at' => $token->last_used_at,
            ],
        ]);
    }

    public function destroyDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
        ]);

        UserPushToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json(['message' => 'Device token removed.']);
    }

    public function firebaseConfig(): JsonResponse
    {
        return response()->json([
            'enabled' => (bool) config('firebase.enabled'),
            'project_id' => config('firebase.project_id'),
            'android_channel_id' => config('firebase.android_channel_id'),
            'topics' => [
                'engineer_tasks',
                'inspection_updates',
            ],
        ]);
    }

    private function ticketNotifications(int $userId, int $limit): Collection
    {
        return Ticket::query()
            ->with(['status:id,name,code', 'priority:id,name'])
            ->where('assigned_engineer_id', $userId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (Ticket $ticket): array {
                $isCompleted = $ticket->completed_at !== null;
                $title = $isCompleted ? 'Ticket selesai' : 'Update ticket assigned';
                $message = sprintf(
                    '%s - %s (%s)',
                    $ticket->ticket_number ?? '-',
                    $ticket->title ?? '-',
                    $ticket->status?->name ?? 'Unknown',
                );

                return [
                    'id' => 'ticket-'.$ticket->id,
                    'type' => 'ticket',
                    'title' => $title,
                    'message' => $message,
                    'priority' => $ticket->priority?->name,
                    'status' => $ticket->status?->name,
                    'ticket_id' => $ticket->id,
                    'inspection_id' => null,
                    'route' => [
                        'screen' => 'task_detail',
                        'params' => ['ticket_id' => $ticket->id],
                    ],
                    'is_unread' => $ticket->updated_at?->gt(now()->subDay()) ?? false,
                    'occurred_at' => $ticket->updated_at?->toIso8601String(),
                ];
            });
    }

    private function inspectionNotifications(int $userId, int $limit): Collection
    {
        return Inspection::query()
            ->with(['ticket:id,inspection_id,ticket_number'])
            ->where('inspection_officer_id', $userId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (Inspection $inspection): array {
                $hasTicket = $inspection->ticket !== null;
                $title = $inspection->final_result === Inspection::FINAL_RESULT_ABNORMAL
                    ? 'Inspection abnormal'
                    : 'Update inspection';
                $message = sprintf(
                    '%s - %s%s',
                    $inspection->inspection_number ?? '-',
                    ucfirst((string) $inspection->status),
                    $hasTicket ? ' (ticket '.$inspection->ticket?->ticket_number.')' : '',
                );

                return [
                    'id' => 'inspection-'.$inspection->id,
                    'type' => 'inspection',
                    'title' => $title,
                    'message' => $message,
                    'priority' => $inspection->final_result === Inspection::FINAL_RESULT_ABNORMAL ? 'High' : 'Normal',
                    'status' => $inspection->status,
                    'ticket_id' => $inspection->ticket?->id,
                    'inspection_id' => $inspection->id,
                    'route' => [
                        'screen' => 'inspection_detail',
                        'params' => ['inspection_id' => $inspection->id],
                    ],
                    'is_unread' => $inspection->updated_at?->gt(now()->subDay()) ?? false,
                    'occurred_at' => $inspection->updated_at?->toIso8601String(),
                ];
            });
    }
}

