<?php

namespace App\Modules\MasterData\Users\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Modules\MasterData\Users\Requests\StoreUserRequest;
use App\Modules\MasterData\Users\Requests\UpdateUserRequest;
use App\Modules\MasterData\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'department_id' => $request->input('department_id'),
        ];

        $users = $this->userService->paginate($filters, (int) $request->input('per_page', 15));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        return new UserResource($this->userService->create($request->validated()));
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['department:id,name', 'roleRef:id,code,name']));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->userService->update($user, $request->validated()));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ((int) $request->user()?->id === (int) $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $this->userService->delete($user);

        return response()->json(['message' => 'User deleted.']);
    }
}

