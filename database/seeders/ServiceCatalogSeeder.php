<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->pluck('id', 'code');
        $vendors = Vendor::query()->pluck('id', 'code');

        $services = [
            ['code' => 'SRV-WIFI-CORE', 'name' => 'Corporate WiFi Core Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Centralized wireless controller, SSID management, and policy backbone for all office and field sites.'],
            ['code' => 'SRV-WIFI-AREA', 'name' => 'Area Wireless Access Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Distributed access point operation across depots, gates, and operational areas.'],
            ['code' => 'SRV-CCTV-MON', 'name' => 'CCTV Monitoring Service', 'service_category' => 'Security', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-SEC-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Live monitoring, recording, and fault handling for security camera systems.'],
            ['code' => 'SRV-GATE-SCAN', 'name' => 'Gate Scanner Service', 'service_category' => 'Security', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-ACS-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_VENDOR, 'description' => 'Entry gate scanner and checkpoint validation system used by field operations.'],
            ['code' => 'SRV-LAN-EDGE', 'name' => 'LAN Edge Switching Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'Managed switching and access layer connectivity for core office and branch users.'],
            ['code' => 'SRV-WAN-UPLINK', 'name' => 'WAN Uplink Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_VENDOR, 'description' => 'Primary MPLS, internet, and branch uplink connectivity for remote sites.'],
            ['code' => 'SRV-FW-PERIM', 'name' => 'Perimeter Firewall Service', 'service_category' => 'Security', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'Edge security gateway, segmentation, and perimeter policy enforcement.'],
            ['code' => 'SRV-UPS-POWER', 'name' => 'Critical UPS Power Service', 'service_category' => 'Infrastructure', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-UPS-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'UPS-backed power assurance for communication rooms, gates, and server equipment.'],
            ['code' => 'SRV-DC-NET', 'name' => 'Data Center Network Service', 'service_category' => 'Infrastructure', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'Server room switching, distribution, and core backbone for shared infrastructure.'],
            ['code' => 'SRV-IOT-EDGE', 'name' => 'IoT Edge Connectivity Service', 'service_category' => 'Operations Technology', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Connectivity layer for field sensors, telemetry, and edge devices.'],
            ['code' => 'SRV-ACS-CONTROL', 'name' => 'Access Control Service', 'service_category' => 'Security', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-ACS-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_VENDOR, 'description' => 'Card reader, access controller, and central permission management platform.'],
            ['code' => 'SRV-NVR-STORE', 'name' => 'NVR Storage Service', 'service_category' => 'Security', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-SEC-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Video retention, playback, and recording storage service for CCTV operations.'],
            ['code' => 'SRV-RADIO-BH', 'name' => 'Radio Backhaul Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_VENDOR, 'description' => 'Point-to-point wireless backhaul used for sites without fiber redundancy.'],
            ['code' => 'SRV-SAT-BACKUP', 'name' => 'Satellite Backup Service', 'service_category' => 'Connectivity', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_VENDOR, 'description' => 'Emergency connectivity fallback for isolated and critical sites.'],
            ['code' => 'SRV-CLOUD-GW', 'name' => 'Cloud Gateway Service', 'service_category' => 'Infrastructure', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'Gateway service for secure connectivity between on-prem and cloud workloads.'],
            ['code' => 'SRV-FIELD-MAINT', 'name' => 'Field Maintenance Service', 'service_category' => 'Field Support', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'Planned field visit, corrective intervention, and urgent on-site support service.'],
            ['code' => 'SRV-ASSET-DEPLOY', 'name' => 'Asset Deployment Service', 'service_category' => 'Field Support', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL, 'description' => 'New device rollout, relocation, MAC activity, and replacement deployment coordination.'],
            ['code' => 'SRV-CABLE-MGMT', 'name' => 'Structured Cabling Service', 'service_category' => 'Infrastructure', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'ownership_model' => ServiceCatalog::OWNERSHIP_HYBRID, 'description' => 'Fiber, copper, and patching service for stable field and office connectivity.'],
        ];

        foreach ($services as $service) {
            ServiceCatalog::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'service_category' => $service['service_category'],
                    'description' => $service['description'],
                    'ownership_model' => $service['ownership_model'],
                    'department_owner_id' => $departments[$service['department_code']] ?? null,
                    'vendor_id' => $vendors[$service['vendor_code']] ?? null,
                    'service_manager_user_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
