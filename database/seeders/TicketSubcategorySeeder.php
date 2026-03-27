<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = TicketCategory::query()->pluck('id', 'code');
        $approvers = User::query()->pluck('id', 'email');

        $subcategories = [
            ['category_code' => 'INCIDENT', 'code' => 'NETWORK_DOWN', 'name' => 'Network Down', 'description' => 'Hard outage impacting connectivity or communication path.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'INCIDENT', 'code' => 'PERFORMANCE', 'name' => 'Performance Degradation', 'description' => 'Service still runs but with unstable or degraded quality.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'INCIDENT', 'code' => 'SECURITY_ALERT', 'name' => 'Security Alert', 'description' => 'Potential security event requiring operational response.', 'requires_approval' => false, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
            ['category_code' => 'INCIDENT', 'code' => 'DEVICE_FAILURE', 'name' => 'Device Failure', 'description' => 'Hardware or component issue causing disruption.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'INCIDENT', 'code' => 'ACCESS_ISSUE', 'name' => 'Access Issue', 'description' => 'User or site unable to access protected or managed service.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'INCIDENT', 'code' => 'POWER_ISSUE', 'name' => 'Power Issue', 'description' => 'Power instability, UPS failure, or electricity impact to service.', 'requires_approval' => false, 'allow_direct_assignment' => true],

            ['category_code' => 'REQUEST', 'code' => 'NEW_INSTALL', 'name' => 'New Installation', 'description' => 'Request for new device, link, or site activation.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'requester_department_head', 'approver_role_code' => null],
            ['category_code' => 'REQUEST', 'code' => 'ACCESS_REQUEST', 'name' => 'Access Request', 'description' => 'Request for account, VPN, access permission, or entitlement change.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
            ['category_code' => 'REQUEST', 'code' => 'MOVE_ADD_CHANGE', 'name' => 'Move / Add / Change', 'description' => 'Service relocation, expansion, and structured operational change request.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => $approvers['supervisor@demo.com'] ?? null, 'approver_strategy' => 'specific_user', 'approver_role_code' => null],
            ['category_code' => 'REQUEST', 'code' => 'USER_ONBOARDING', 'name' => 'User Onboarding', 'description' => 'Bundle request for newly onboarded staff operational access.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'requester_department_head', 'approver_role_code' => null],
            ['category_code' => 'REQUEST', 'code' => 'SERVICE_ACTIVATION', 'name' => 'Service Activation', 'description' => 'Enablement request for site, service, or monitoring capability.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'service_manager', 'approver_role_code' => null],

            ['category_code' => 'MAINTENANCE', 'code' => 'PREVENTIVE', 'name' => 'Preventive Maintenance', 'description' => 'Routine inspection and preventive activity to keep service healthy.', 'requires_approval' => false, 'allow_direct_assignment' => false],
            ['category_code' => 'MAINTENANCE', 'code' => 'CORRECTIVE', 'name' => 'Corrective Maintenance', 'description' => 'Corrective action to restore asset or service health.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'MAINTENANCE', 'code' => 'INSPECTION_FOLLOW_UP', 'name' => 'Inspection Follow Up', 'description' => 'Follow-up action generated from inspection abnormality or finding.', 'requires_approval' => false, 'allow_direct_assignment' => true],
            ['category_code' => 'MAINTENANCE', 'code' => 'PART_REPLACEMENT', 'name' => 'Part Replacement', 'description' => 'Planned replacement for damaged or aging component.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
        ];

        foreach ($subcategories as $subcategory) {
            $categoryId = $categories[$subcategory['category_code']] ?? null;
            if ($categoryId === null) {
                continue;
            }

            TicketSubcategory::query()->updateOrCreate(
                [
                    'ticket_category_id' => $categoryId,
                    'code' => $subcategory['code'],
                ],
                [
                    'name' => $subcategory['name'],
                    'description' => $subcategory['description'],
                    'requires_approval' => $subcategory['requires_approval'],
                    'allow_direct_assignment' => $subcategory['allow_direct_assignment'],
                    'approver_user_id' => $subcategory['approver_user_id'] ?? null,
                    'approver_strategy' => $subcategory['approver_strategy'] ?? null,
                    'approver_role_code' => $subcategory['approver_role_code'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
