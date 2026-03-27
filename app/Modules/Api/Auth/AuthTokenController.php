<?php

namespace App\Modules\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Models\User;
use App\Modules\MasterData\Users\UserService;
use App\Modules\Profile\Requests\UpdateProfileRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $plainTextToken = bin2hex(random_bytes(40));
        $expiresAt = isset($credentials['expires_in_hours'])
            ? CarbonImmutable::now()->addHours((int) $credentials['expires_in_hours'])
            : null;

        $token = ApiToken::query()->create([
            'user_id' => $user->id,
            'name' => $credentials['device_name'] ?? 'mobile-app',
            'token' => hash('sha256', $plainTextToken),
            'abilities' => ['*'],
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'token' => $plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at,
            'user' => new UserResource($user->load(['department', 'roleRef'])),
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['department', 'roleRef']));
    }

    public function updateMe(UpdateProfileRequest $request): UserResource
    {
        $updatedUser = $this->userService->updateProfile($request->user(), $request->validated());

        return new UserResource($updatedUser);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');

        if ($token !== null) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }
}
