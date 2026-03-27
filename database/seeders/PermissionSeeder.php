<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('rbac.catalog', []) as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'group_name' => $permission['group'] ?? null,
                    'description' => $permission['description'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
