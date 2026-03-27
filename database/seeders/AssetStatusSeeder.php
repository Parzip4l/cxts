<?php

namespace Database\Seeders;

use App\Models\AssetStatus;
use Illuminate\Database\Seeder;

class AssetStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'ACTIVE', 'name' => 'Active', 'description' => 'Operational and currently used in production service.', 'is_operational' => true],
            ['code' => 'STANDBY', 'name' => 'Standby', 'description' => 'Ready as backup device and can be activated when needed.', 'is_operational' => true],
            ['code' => 'MAINT', 'name' => 'Maintenance', 'description' => 'Temporarily unavailable because of planned maintenance activity.', 'is_operational' => false],
            ['code' => 'FAULTY', 'name' => 'Faulty', 'description' => 'Known issue exists and device is not considered healthy for production.', 'is_operational' => false],
            ['code' => 'RETIRED', 'name' => 'Retired', 'description' => 'Asset already decommissioned and kept only for historical reference.', 'is_operational' => false],
        ];

        foreach ($statuses as $status) {
            AssetStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'description' => $status['description'],
                    'is_operational' => $status['is_operational'],
                    'is_active' => true,
                ]
            );
        }
    }
}
