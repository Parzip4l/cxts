<?php

namespace App\Modules\MasterData\Users\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EngineerSkill;
use App\Models\Role;
use App\Models\User;
use App\Modules\MasterData\Users\Requests\StoreUserRequest;
use App\Modules\MasterData\Users\Requests\UpdateUserRequest;
use App\Modules\MasterData\Users\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'department_id' => $request->input('department_id'),
        ];

        return view('modules.master-data.users.index', [
            'users' => $this->userService->paginate($filters),
            'filters' => $filters,
            'roleOptions' => Role::query()->where('is_active', true)->orderBy('name')->get(['code', 'name']),
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.users.form', [
            'userRecord' => new User(),
            'action' => route('master-data.users.store'),
            'method' => 'POST',
            'pageTitle' => 'Create User',
            'roleOptions' => Role::query()->where('is_active', true)->orderBy('name')->get(['code', 'name']),
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('master-data.users.index')
            ->with('success', 'User has been created.');
    }

    public function edit(User $user): View
    {
        return view('modules.master-data.users.form', [
            'userRecord' => $user,
            'action' => route('master-data.users.update', $user),
            'method' => 'PUT',
            'pageTitle' => 'Edit User',
            'roleOptions' => Role::query()->where('is_active', true)->orderBy('name')->get(['code', 'name']),
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('master-data.users.index')
            ->with('success', 'User has been updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()?->id === (int) $user->id) {
            return redirect()
                ->route('master-data.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $this->userService->delete($user);

        return redirect()
            ->route('master-data.users.index')
            ->with('success', 'User has been deleted.');
    }

    public function profilePhoto(Request $request, User $user): StreamedResponse
    {
        abort_unless($request->user() !== null, 403);

        if (
            (int) $request->user()->id !== (int) $user->id
            && ! $request->user()->hasAnyPermission(['dashboard.view_ops', 'workforce.manage', 'organization.manage', 'engineer_task.view_assigned'])
        ) {
            abort(403);
        }

        abort_unless($user->profile_photo_path, 404);
        abort_unless(Storage::disk('local')->exists($user->profile_photo_path), 404);

        return Storage::disk('local')->response($user->profile_photo_path);
    }
}
