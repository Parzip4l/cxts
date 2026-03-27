<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $approvers = User::query()->pluck('id', 'email');

        $categories = [
            ['code' => 'INCIDENT', 'name' => 'Incident', 'description' => 'Unplanned interruption or quality degradation', 'requires_approval' => false, 'allow_direct_assignment' => true, 'approver_user_id' => null, 'approver_strategy' => 'fallback', 'approver_role_code' => null],
            ['code' => 'REQUEST', 'name' => 'Service Request', 'description' => 'Standard service request from requester', 'requires_approval' => false, 'allow_direct_assignment' => true, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'supervisor'],
            ['code' => 'MAINTENANCE', 'name' => 'Maintenance', 'description' => 'Planned or corrective maintenance activity', 'requires_approval' => false, 'allow_direct_assignment' => true, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
        ];

        foreach ($categories as $category) {
            TicketCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'requires_approval' => $category['requires_approval'],
                    'allow_direct_assignment' => $category['allow_direct_assignment'],
                    'approver_user_id' => $category['approver_user_id'],
                    'approver_strategy' => $category['approver_strategy'],
                    'approver_role_code' => $category['approver_role_code'],
                    'is_active' => true,
                ]
            );
        }
    }
}
