<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'super_admin', 'name' => 'Super Admin', 'description' => 'Full access to all modules.'],
            ['code' => 'operational_admin', 'name' => 'Operational Admin', 'description' => 'Operational and master data administration.'],
            ['code' => 'supervisor', 'name' => 'Supervisor', 'description' => 'Supervision, assignment and monitoring.'],
            ['code' => 'engineer', 'name' => 'Engineer', 'description' => 'Assigned task execution.'],
            ['code' => 'inspection_officer', 'name' => 'Inspection Officer', 'description' => 'Inspection execution and reporting.'],
            ['code' => 'requester', 'name' => 'Requester', 'description' => 'Ticket requester role.'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
