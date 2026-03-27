<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\InspectionTemplate;
use Illuminate\Database\Seeder;

class InspectionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $assetCategories = AssetCategory::query()->pluck('id', 'code');

        $templates = [
            [
                'code' => 'INSP-WIFI-DAILY',
                'name' => 'Daily Wireless Access Inspection',
                'description' => 'Daily checklist for access point health, connectivity, and physical condition.',
                'asset_category_code' => 'CAT-AP',
                'items' => [
                    ['sequence' => 1, 'item_label' => 'Power indicator is normal', 'item_type' => 'boolean', 'expected_value' => 'ON'],
                    ['sequence' => 2, 'item_label' => 'SSID broadcast is available', 'item_type' => 'boolean', 'expected_value' => 'AVAILABLE'],
                    ['sequence' => 3, 'item_label' => 'Internet connectivity test', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                    ['sequence' => 4, 'item_label' => 'Average latency (ms)', 'item_type' => 'number', 'expected_value' => '<50'],
                    ['sequence' => 5, 'item_label' => 'Physical condition note', 'item_type' => 'text', 'expected_value' => null],
                ],
            ],
            [
                'code' => 'INSP-CCTV-WEEKLY',
                'name' => 'Weekly CCTV Health Inspection',
                'description' => 'Weekly surveillance quality and recorder connectivity verification.',
                'asset_category_code' => 'CAT-CCTV',
                'items' => [
                    ['sequence' => 1, 'item_label' => 'Camera feed available', 'item_type' => 'boolean', 'expected_value' => 'AVAILABLE'],
                    ['sequence' => 2, 'item_label' => 'Lens and housing condition normal', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                    ['sequence' => 3, 'item_label' => 'Night vision test result', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                    ['sequence' => 4, 'item_label' => 'Recording verification', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                ],
            ],
            [
                'code' => 'INSP-UPS-WEEKLY',
                'name' => 'Weekly UPS Readiness Inspection',
                'description' => 'Weekly UPS battery, load, and alarm readiness verification.',
                'asset_category_code' => 'CAT-UPS',
                'items' => [
                    ['sequence' => 1, 'item_label' => 'UPS alarm status normal', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                    ['sequence' => 2, 'item_label' => 'Battery health status', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                    ['sequence' => 3, 'item_label' => 'Current load percentage', 'item_type' => 'number', 'expected_value' => '<80'],
                    ['sequence' => 4, 'item_label' => 'Runtime estimation note', 'item_type' => 'text', 'expected_value' => null],
                ],
            ],
        ];

        foreach ($templates as $definition) {
            $template = InspectionTemplate::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'asset_category_id' => $assetCategories[$definition['asset_category_code']] ?? null,
                    'is_active' => true,
                ]
            );

            $template->items()->delete();

            foreach ($definition['items'] as $item) {
                $template->items()->create([
                    ...$item,
                    'is_required' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
