<?php

namespace App\Modules\MasterData\RolePermissions\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('modules.master-data.role-permissions.index', [
            'roles' => Role::query()->withCount('permissions')->orderBy('name')->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $permissions = Permission::query()
            ->where('is_active', true)
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->group_name ?: 'other');

        return view('modules.master-data.role-permissions.form', [
            'roleRecord' => $role->load('permissions:id,code'),
            'permissionGroups' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $permissionIds = collect($request->input('permission_ids', []))
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $validPermissionIds = Permission::query()
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $role->permissions()->sync($validPermissionIds);

        return redirect()
            ->route('master-data.role-permissions.index')
            ->with('success', 'Role permission matrix has been updated.');
    }
}
