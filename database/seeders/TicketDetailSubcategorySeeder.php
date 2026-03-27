<?php

namespace Database\Seeders;

use App\Models\TicketDetailSubcategory;
use App\Models\TicketSubcategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketDetailSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $approvers = User::query()->pluck('id', 'email');

        $definitions = [
            'NETWORK_DOWN' => [
                ['code' => 'LAN_OUTAGE', 'name' => 'LAN Outage', 'description' => 'User floor or edge LAN segment fully unavailable.'],
                ['code' => 'WAN_OUTAGE', 'name' => 'WAN Outage', 'description' => 'Branch or site uplink disconnected from central network.'],
                ['code' => 'WIRELESS_OUTAGE', 'name' => 'Wireless Outage', 'description' => 'SSID or AP service unavailable for user devices.'],
                ['code' => 'BACKBONE_LINK_DOWN', 'name' => 'Backbone Link Down', 'description' => 'Core backbone or inter-building link has failed.'],
            ],
            'PERFORMANCE' => [
                ['code' => 'HIGH_LATENCY', 'name' => 'High Latency', 'description' => 'Transaction delay above normal threshold.'],
                ['code' => 'PACKET_LOSS', 'name' => 'Packet Loss', 'description' => 'Unstable delivery causing retries and intermittent use.'],
                ['code' => 'INTERMITTENT_ACCESS', 'name' => 'Intermittent Access', 'description' => 'Users can connect but service frequently drops.'],
                ['code' => 'BANDWIDTH_CONGESTION', 'name' => 'Bandwidth Congestion', 'description' => 'Traffic congestion reducing usable throughput.'],
            ],
            'SECURITY_ALERT' => [
                ['code' => 'UNAUTHORIZED_ACCESS', 'name' => 'Unauthorized Access', 'description' => 'Potential unauthorized access attempt detected.'],
                ['code' => 'FIREWALL_ANOMALY', 'name' => 'Firewall Anomaly', 'description' => 'Security gateway behavior outside baseline.'],
                ['code' => 'CCTV_BLIND_SPOT', 'name' => 'CCTV Blind Spot', 'description' => 'Critical surveillance area not covered or camera feed missing.'],
            ],
            'DEVICE_FAILURE' => [
                ['code' => 'HARDWARE_FAILURE', 'name' => 'Hardware Failure', 'description' => 'Physical device failure requiring replacement or repair.'],
                ['code' => 'MODULE_FAILURE', 'name' => 'Module Failure', 'description' => 'Specific module, PSU, or interface component failure.'],
                ['code' => 'STORAGE_FAILURE', 'name' => 'Storage Failure', 'description' => 'Disk or storage subsystem issue on recorder or server.'],
            ],
            'ACCESS_ISSUE' => [
                ['code' => 'ACCOUNT_ACCESS', 'name' => 'Account Access', 'description' => 'User account unable to sign in or access standard function.'],
                ['code' => 'VPN_ACCESS', 'name' => 'VPN Access', 'description' => 'Remote tunnel access unavailable or rejected.'],
                ['code' => 'PERMISSION_CHANGE', 'name' => 'Permission Change', 'description' => 'Role or permission issue preventing expected access.'],
                ['code' => 'CARD_READER_REJECT', 'name' => 'Card Reader Reject', 'description' => 'Access card or reader rejects valid user at checkpoint.'],
            ],
            'POWER_ISSUE' => [
                ['code' => 'UPS_ALARM', 'name' => 'UPS Alarm', 'description' => 'UPS enters warning or critical alarm condition.'],
                ['code' => 'POWER_DROP', 'name' => 'Power Drop', 'description' => 'Observed power instability impacting service continuity.'],
                ['code' => 'BATTERY_DEGRADATION', 'name' => 'Battery Degradation', 'description' => 'Battery health no longer within safe operational range.'],
            ],
            'NEW_INSTALL' => [
                ['code' => 'NEW_DEVICE_INSTALL', 'name' => 'New Device Install', 'description' => 'Install new endpoint, AP, camera, or related field device.'],
                ['code' => 'NEW_LINK_INSTALL', 'name' => 'New Link Install', 'description' => 'Provision new WAN, LAN, or structured link.'],
                ['code' => 'SITE_ACTIVATION', 'name' => 'Site Activation', 'description' => 'Enable operational technology stack for a new site.'],
                ['code' => 'NEW_RACK_SETUP', 'name' => 'New Rack Setup', 'description' => 'Build new rack and supporting field infrastructure.'],
            ],
            'ACCESS_REQUEST' => [
                ['code' => 'ACCOUNT_ACCESS', 'name' => 'Account Access', 'description' => 'Create or enable standard user account access.', 'requires_approval' => false, 'allow_direct_assignment' => true],
                ['code' => 'VPN_ACCESS', 'name' => 'VPN Access', 'description' => 'Provision remote access connectivity.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
                ['code' => 'PERMISSION_CHANGE', 'name' => 'Permission Change', 'description' => 'Adjust approval-based permission scope.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'operational_admin'],
                ['code' => 'TEMPORARY_ACCESS', 'name' => 'Temporary Access', 'description' => 'Time-bound access request for project or vendor activity.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'requester_department_head', 'approver_role_code' => null],
            ],
            'MOVE_ADD_CHANGE' => [
                ['code' => 'DEVICE_RELOCATION', 'name' => 'Device Relocation', 'description' => 'Move an existing device to a new point or room.', 'requires_approval' => false, 'allow_direct_assignment' => false],
                ['code' => 'PORT_ADDITION', 'name' => 'Port Addition', 'description' => 'Add switch port, patch, or access endpoint for a site.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'requester_department_head', 'approver_role_code' => null],
                ['code' => 'TOPOLOGY_CHANGE', 'name' => 'Topology Change', 'description' => 'Planned change in connectivity design or routing path.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => $approvers['opsadmin@demo.com'] ?? null, 'approver_strategy' => 'specific_user', 'approver_role_code' => null],
            ],
            'USER_ONBOARDING' => [
                ['code' => 'NEW_JOINER_BUNDLE', 'name' => 'New Joiner Bundle', 'description' => 'Standard onboarding access package for new employee.'],
                ['code' => 'FIELD_USER_ACTIVATION', 'name' => 'Field User Activation', 'description' => 'Enable operational access for field engineer or officer.'],
            ],
            'SERVICE_ACTIVATION' => [
                ['code' => 'MONITORING_ENABLEMENT', 'name' => 'Monitoring Enablement', 'description' => 'Activate monitoring for newly onboarded device or site.'],
                ['code' => 'SERVICE_GO_LIVE', 'name' => 'Service Go-Live', 'description' => 'Final service activation after deployment and readiness checks.'],
            ],
            'PREVENTIVE' => [
                ['code' => 'HEALTH_CHECK', 'name' => 'Health Check', 'description' => 'Routine inspection to confirm operating health.', 'requires_approval' => false, 'allow_direct_assignment' => false],
                ['code' => 'CLEANING', 'name' => 'Cleaning', 'description' => 'Preventive physical cleaning and dust management.', 'requires_approval' => false, 'allow_direct_assignment' => false],
                ['code' => 'SCHEDULED_TEST', 'name' => 'Scheduled Test', 'description' => 'Periodic test to validate failover or operational readiness.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'role_based', 'approver_role_code' => 'supervisor'],
                ['code' => 'FIRMWARE_REVIEW', 'name' => 'Firmware Review', 'description' => 'Planned review and validation of firmware compliance.', 'requires_approval' => true, 'allow_direct_assignment' => false, 'approver_user_id' => null, 'approver_strategy' => 'service_manager', 'approver_role_code' => null],
            ],
            'CORRECTIVE' => [
                ['code' => 'COMPONENT_REPLACEMENT', 'name' => 'Component Replacement', 'description' => 'Replace failing component with working part.'],
                ['code' => 'REPAIR_VISIT', 'name' => 'Repair Visit', 'description' => 'On-site repair action for failed or degraded equipment.'],
                ['code' => 'EMERGENCY_FIX', 'name' => 'Emergency Fix', 'description' => 'Urgent corrective work to restore a critical service.'],
                ['code' => 'RECONFIGURATION', 'name' => 'Reconfiguration', 'description' => 'Corrective reconfiguration after operational issue.'],
            ],
            'INSPECTION_FOLLOW_UP' => [
                ['code' => 'ABNORMAL_INSPECTION', 'name' => 'Abnormal Inspection Follow Up', 'description' => 'Follow-up work generated from abnormal inspection result.'],
                ['code' => 'SITE_RECTIFICATION', 'name' => 'Site Rectification', 'description' => 'Rectification visit after inspection finding at field site.'],
            ],
            'PART_REPLACEMENT' => [
                ['code' => 'BATTERY_REPLACEMENT', 'name' => 'Battery Replacement', 'description' => 'Replace battery module in UPS or field device.'],
                ['code' => 'CAMERA_REPLACEMENT', 'name' => 'Camera Replacement', 'description' => 'Replace surveillance camera unit that is no longer serviceable.'],
                ['code' => 'SFP_REPLACEMENT', 'name' => 'SFP Replacement', 'description' => 'Replace optic module on switch, router, or backbone equipment.'],
            ],
        ];

        foreach ($definitions as $subcategoryCode => $items) {
            $subcategory = TicketSubcategory::query()->where('code', $subcategoryCode)->first();
            if ($subcategory === null) {
                continue;
            }

            foreach ($items as $item) {
                TicketDetailSubcategory::query()->updateOrCreate(
                    [
                        'ticket_subcategory_id' => $subcategory->id,
                        'code' => $item['code'],
                    ],
                    [
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'requires_approval' => $item['requires_approval'] ?? null,
                        'allow_direct_assignment' => $item['allow_direct_assignment'] ?? null,
                        'approver_user_id' => $item['approver_user_id'] ?? null,
                        'approver_strategy' => $item['approver_strategy'] ?? null,
                        'approver_role_code' => $item['approver_role_code'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
