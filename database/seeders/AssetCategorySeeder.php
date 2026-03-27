<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT-AP', 'name' => 'Access Point', 'description' => 'Wireless access point devices for user and area coverage.'],
            ['code' => 'CAT-SW', 'name' => 'Access Switch', 'description' => 'Managed switching devices at edge and distribution layer.'],
            ['code' => 'CAT-RT', 'name' => 'Router', 'description' => 'Routing devices for branch, WAN, and site uplink traffic.'],
            ['code' => 'CAT-FW', 'name' => 'Firewall', 'description' => 'Perimeter and segmentation security appliances.'],
            ['code' => 'CAT-CCTV', 'name' => 'CCTV Camera', 'description' => 'Security surveillance camera endpoints.'],
            ['code' => 'CAT-GATE', 'name' => 'Gate Scanner', 'description' => 'Gate and checkpoint scanning devices used for access validation.'],
            ['code' => 'CAT-UPS', 'name' => 'UPS Unit', 'description' => 'Uninterruptible power supply equipment for critical loads.'],
            ['code' => 'CAT-SRV', 'name' => 'Server', 'description' => 'Physical or edge server infrastructure.'],
            ['code' => 'CAT-STO', 'name' => 'Storage', 'description' => 'Storage arrays, NAS, or backup storage devices.'],
            ['code' => 'CAT-NVR', 'name' => 'Network Video Recorder', 'description' => 'Video recording and retention hardware.'],
            ['code' => 'CAT-IOT', 'name' => 'IoT Device', 'description' => 'Field telemetry, sensor, and operational IoT endpoint.'],
            ['code' => 'CAT-ACS', 'name' => 'Access Controller', 'description' => 'Door, turnstile, or barrier access controller devices.'],
            ['code' => 'CAT-RADIO', 'name' => 'Radio Backhaul', 'description' => 'Wireless point-to-point or outdoor radio backhaul unit.'],
            ['code' => 'CAT-MODEM', 'name' => 'Modem', 'description' => 'Satellite, cellular, or WAN modem endpoint.'],
            ['code' => 'CAT-RACK', 'name' => 'Rack & Cabinet', 'description' => 'Rack, cabinet, and physical housing infrastructure.'],
            ['code' => 'CAT-CABLE', 'name' => 'Structured Cabling', 'description' => 'Fiber backbone, copper links, and patch infrastructure.'],
        ];

        foreach ($categories as $category) {
            AssetCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
