<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('rbac.role_defaults', []) as $roleCode => $permissionCodes) {
            $role = Role::query()->where('code', $roleCode)->first();

            if (! $role) {
                continue;
            }

            if (in_array('*', $permissionCodes, true)) {
                $role->permissions()->sync(Permission::query()->pluck('id')->all());

                continue;
            }

            $permissionIds = Permission::query()
                ->whereIn('code', $permissionCodes)
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
