<?php

namespace App\Modules\MasterData\Permissions\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'group_name' => $request->input('group_name'),
        ];

        $permissions = Permission::query()
            ->when($filters['search'], fn ($query, $search) => $query
                ->where(fn ($subQuery) => $subQuery
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")))
            ->when($filters['group_name'], fn ($query, $group) => $query->where('group_name', $group))
            ->orderBy('group_name')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('modules.master-data.permissions.index', [
            'permissions' => $permissions,
            'filters' => $filters,
            'groupOptions' => Permission::query()->whereNotNull('group_name')->distinct()->orderBy('group_name')->pluck('group_name'),
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.permissions.form', [
            'permissionRecord' => new Permission(),
            'action' => route('master-data.permissions.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Permission',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Permission::query()->create($data);

        return redirect()
            ->route('master-data.permissions.index')
            ->with('success', 'Permission has been created.');
    }

    public function edit(Permission $permission): View
    {
        return view('modules.master-data.permissions.form', [
            'permissionRecord' => $permission,
            'action' => route('master-data.permissions.update', $permission),
            'method' => 'PUT',
            'pageTitle' => 'Edit Permission',
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $this->validated($request, $permission);
        $permission->update($data);

        return redirect()
            ->route('master-data.permissions.index')
            ->with('success', 'Permission has been updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('master-data.permissions.index')
            ->with('success', 'Permission has been deleted.');
    }

    private function validated(Request $request, ?Permission $permission = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('permissions', 'code')->ignore($permission?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => (bool) $request->boolean('is_active', true),
        ];
    }
}
