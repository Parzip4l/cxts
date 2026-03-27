<?php

namespace App\Modules\Profile\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Modules\MasterData\Users\UserService;
use App\Modules\Profile\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function edit(Request $request): View
    {
        return view('modules.profile.edit', [
            'userRecord' => $request->user()->load(['department:id,name', 'roleRef:id,code,name']),
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userService->updateProfile($request->user(), $request->validated());

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile has been updated.');
    }
}

