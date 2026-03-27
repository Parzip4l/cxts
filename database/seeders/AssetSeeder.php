<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = AssetCategory::query()->pluck('id', 'code');
        $statuses = AssetStatus::query()->pluck('id', 'code');
        $locations = AssetLocation::query()->pluck('id', 'code');
        $departments = Department::query()->pluck('id', 'code');
        $services = ServiceCatalog::query()->pluck('id', 'code');
        $vendors = Vendor::query()->pluck('id', 'code');

        $assets = [
            ['code' => 'AST-AP-001', 'name' => 'Access Point Jakarta HQ Lobby', 'category_code' => 'CAT-AP', 'service_code' => 'SRV-WIFI-CORE', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-JKT-001', 'serial' => 'APSN-JKT-0001', 'brand' => 'Cisco', 'model' => 'Aironet 2800', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Main lobby coverage device for guest and staff access.'],
            ['code' => 'AST-SW-001', 'name' => 'Distribution Switch Jakarta HQ Floor 3', 'category_code' => 'CAT-SW', 'service_code' => 'SRV-LAN-EDGE', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-JKT-001', 'serial' => 'SWSN-JKT-3001', 'brand' => 'Cisco', 'model' => 'Catalyst 9300', 'criticality' => Asset::CRITICALITY_CRITICAL, 'status_code' => 'ACTIVE', 'notes' => 'Primary floor distribution switch connected to backbone uplink.'],
            ['code' => 'AST-RT-001', 'name' => 'WAN Router Bekasi Depot', 'category_code' => 'CAT-RT', 'service_code' => 'SRV-WAN-UPLINK', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'location_code' => 'LOC-BKS-001', 'serial' => 'RTSN-BKS-0101', 'brand' => 'Cisco', 'model' => 'ISR 4331', 'criticality' => Asset::CRITICALITY_CRITICAL, 'status_code' => 'ACTIVE', 'notes' => 'Primary branch uplink router for depot operations.'],
            ['code' => 'AST-FW-001', 'name' => 'Perimeter Firewall Jakarta HQ', 'category_code' => 'CAT-FW', 'service_code' => 'SRV-FW-PERIM', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-JKT-001', 'serial' => 'FWSN-JKT-0001', 'brand' => 'Fortinet', 'model' => 'FortiGate 200F', 'criticality' => Asset::CRITICALITY_CRITICAL, 'status_code' => 'ACTIVE', 'notes' => 'Main internet edge firewall for headquarters.'],
            ['code' => 'AST-CCTV-001', 'name' => 'CCTV Main Gate Surabaya', 'category_code' => 'CAT-CCTV', 'service_code' => 'SRV-CCTV-MON', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-SEC-001', 'location_code' => 'LOC-SBY-001', 'serial' => 'CCTV-SBY-0001', 'brand' => 'Hikvision', 'model' => 'DS-2CD2T47', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Main gate surveillance coverage for vehicle lane and visitor entry.'],
            ['code' => 'AST-GATE-001', 'name' => 'Gate Scanner Surabaya Lane 1', 'category_code' => 'CAT-GATE', 'service_code' => 'SRV-GATE-SCAN', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-ACS-001', 'location_code' => 'LOC-SBY-001', 'serial' => 'GATE-SBY-0101', 'brand' => 'ZKTeco', 'model' => 'GS-EntryPro', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Primary scanner for inbound access validation at main gate.'],
            ['code' => 'AST-UPS-001', 'name' => 'UPS Jakarta HQ Server Room', 'category_code' => 'CAT-UPS', 'service_code' => 'SRV-UPS-POWER', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-UPS-001', 'location_code' => 'LOC-JKT-001', 'serial' => 'UPS-JKT-0001', 'brand' => 'APC', 'model' => 'Smart-UPS SRT 10K', 'criticality' => Asset::CRITICALITY_CRITICAL, 'status_code' => 'ACTIVE', 'notes' => 'Critical UPS for core network and server room rack.'],
            ['code' => 'AST-SRV-001', 'name' => 'Edge Server Semarang DC-01', 'category_code' => 'CAT-SRV', 'service_code' => 'SRV-DC-NET', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-SMG-001', 'serial' => 'SRV-SMG-0001', 'brand' => 'Dell', 'model' => 'PowerEdge R650', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Regional application and monitoring edge server.'],
            ['code' => 'AST-STO-001', 'name' => 'Storage Appliance Semarang DC-01', 'category_code' => 'CAT-STO', 'service_code' => 'SRV-CLOUD-GW', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-SMG-001', 'serial' => 'STO-SMG-0001', 'brand' => 'Synology', 'model' => 'SA3400', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'STANDBY', 'notes' => 'Backup storage for regional surveillance and file services.'],
            ['code' => 'AST-NVR-001', 'name' => 'NVR Cluster Bekasi Depot', 'category_code' => 'CAT-NVR', 'service_code' => 'SRV-NVR-STORE', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-SEC-001', 'location_code' => 'LOC-BKS-001', 'serial' => 'NVR-BKS-0001', 'brand' => 'Hikvision', 'model' => 'DS-9632NI', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Video retention storage for depot security cameras.'],
            ['code' => 'AST-IOT-001', 'name' => 'Telemetry Gateway Cikarang Hub', 'category_code' => 'CAT-IOT', 'service_code' => 'SRV-IOT-EDGE', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-CKR-001', 'serial' => 'IOT-CKR-0001', 'brand' => 'Advantech', 'model' => 'UNO-2372G', 'criticality' => Asset::CRITICALITY_MEDIUM, 'status_code' => 'ACTIVE', 'notes' => 'Edge gateway aggregating sensor and yard telemetry data.'],
            ['code' => 'AST-ACS-001', 'name' => 'Access Controller Bandung Branch', 'category_code' => 'CAT-ACS', 'service_code' => 'SRV-ACS-CONTROL', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-ACS-001', 'location_code' => 'LOC-BDG-001', 'serial' => 'ACS-BDG-0001', 'brand' => 'Suprema', 'model' => 'CoreStation', 'criticality' => Asset::CRITICALITY_MEDIUM, 'status_code' => 'ACTIVE', 'notes' => 'Central door controller for branch office access points.'],
            ['code' => 'AST-RADIO-001', 'name' => 'Radio Backhaul Medan Yard', 'category_code' => 'CAT-RADIO', 'service_code' => 'SRV-RADIO-BH', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'location_code' => 'LOC-MDN-001', 'serial' => 'RAD-MDN-0001', 'brand' => 'MikroTik', 'model' => 'LHG XL 5', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Primary wireless backhaul for yard operations.'],
            ['code' => 'AST-MDM-001', 'name' => 'Satellite Modem Medan Yard', 'category_code' => 'CAT-MODEM', 'service_code' => 'SRV-SAT-BACKUP', 'department_code' => 'IT-NOC', 'vendor_code' => 'VDR-ISP-001', 'location_code' => 'LOC-MDN-001', 'serial' => 'MDM-MDN-0001', 'brand' => 'Hughes', 'model' => 'HT2300', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'STANDBY', 'notes' => 'Backup connectivity used during WAN outage scenario.'],
            ['code' => 'AST-RACK-001', 'name' => 'Network Rack Makassar Hub', 'category_code' => 'CAT-RACK', 'service_code' => 'SRV-DC-NET', 'department_code' => 'IT-INF', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-MKS-001', 'serial' => 'RACK-MKS-0001', 'brand' => 'Schneider', 'model' => 'NetShelter SX', 'criticality' => Asset::CRITICALITY_MEDIUM, 'status_code' => 'ACTIVE', 'notes' => 'Field hub rack housing router, switch, and UPS modules.'],
            ['code' => 'AST-CBL-001', 'name' => 'Fiber Backbone Segment Bekasi DC', 'category_code' => 'CAT-CABLE', 'service_code' => 'SRV-CABLE-MGMT', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-BKS-001', 'serial' => 'CBL-BKS-0001', 'brand' => 'Furukawa', 'model' => 'OM4 Backbone', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'ACTIVE', 'notes' => 'Core backbone segment between data room and gate distribution cabinet.'],
            ['code' => 'AST-AP-002', 'name' => 'Area Access Point Cikarang Warehouse', 'category_code' => 'CAT-AP', 'service_code' => 'SRV-WIFI-AREA', 'department_code' => 'IT-FOPS', 'vendor_code' => 'VDR-NET-001', 'location_code' => 'LOC-CKR-001', 'serial' => 'APSN-CKR-0002', 'brand' => 'Aruba', 'model' => 'AP-515', 'criticality' => Asset::CRITICALITY_MEDIUM, 'status_code' => 'ACTIVE', 'notes' => 'Warehouse coverage AP with handheld scanner traffic load.'],
            ['code' => 'AST-CCTV-002', 'name' => 'CCTV Yard Perimeter Medan', 'category_code' => 'CAT-CCTV', 'service_code' => 'SRV-CCTV-MON', 'department_code' => 'IT-SEC', 'vendor_code' => 'VDR-SEC-001', 'location_code' => 'LOC-MDN-001', 'serial' => 'CCTV-MDN-0002', 'brand' => 'Dahua', 'model' => 'IPC-HFW5442', 'criticality' => Asset::CRITICALITY_HIGH, 'status_code' => 'FAULTY', 'notes' => 'Known intermittent IR issue reported during night shift.'],
        ];

        foreach ($assets as $asset) {
            Asset::query()->updateOrCreate(
                ['code' => $asset['code']],
                [
                    'name' => $asset['name'],
                    'asset_category_id' => $categories[$asset['category_code']] ?? null,
                    'service_id' => $services[$asset['service_code']] ?? null,
                    'department_owner_id' => $departments[$asset['department_code']] ?? null,
                    'vendor_id' => $vendors[$asset['vendor_code']] ?? null,
                    'asset_location_id' => $locations[$asset['location_code']] ?? null,
                    'serial_number' => $asset['serial'],
                    'brand' => $asset['brand'],
                    'model' => $asset['model'],
                    'install_date' => now()->subMonths(rand(6, 36))->toDateString(),
                    'warranty_end_date' => now()->addMonths(rand(6, 30))->toDateString(),
                    'criticality' => $asset['criticality'],
                    'asset_status_id' => $statuses[$asset['status_code']] ?? null,
                    'notes' => $asset['notes'],
                    'is_active' => true,
                ]
            );
        }
    }
}
