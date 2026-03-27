<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use Illuminate\Database\Seeder;

class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'name' => 'NETWORK_OUTAGE_CRITICAL',
                'description' => 'SLA paling agresif untuk incident network down yang kritikal.',
                'response_time_minutes' => 10,
                'resolution_time_minutes' => 90,
            ],
            [
                'name' => 'PERFORMANCE_DEFAULT',
                'description' => 'Default SLA untuk subcategory performance degradation.',
                'response_time_minutes' => 20,
                'resolution_time_minutes' => 180,
            ],
            [
                'name' => 'STANDARD_24X7',
                'description' => 'Default SLA incident 24x7 untuk operasional umum.',
                'response_time_minutes' => 45,
                'resolution_time_minutes' => 480,
            ],
            [
                'name' => 'REQUEST_STANDARD',
                'description' => 'Default SLA untuk service request standar.',
                'response_time_minutes' => 60,
                'resolution_time_minutes' => 1440,
            ],
            [
                'name' => 'CHANGE_STANDARD',
                'description' => 'Default SLA untuk maintenance atau change execution.',
                'response_time_minutes' => 120,
                'resolution_time_minutes' => 2880,
            ],
            [
                'name' => 'AUTO_ABNORMAL_INSPECTION',
                'description' => 'SLA khusus untuk ticket abnormal inspection yang dibuat otomatis.',
                'response_time_minutes' => 30,
                'resolution_time_minutes' => 240,
            ],
        ];

        foreach ($policies as $policy) {
            SlaPolicy::query()->updateOrCreate(
                ['name' => $policy['name']],
                [
                    'description' => $policy['description'],
                    'response_time_minutes' => $policy['response_time_minutes'],
                    'resolution_time_minutes' => $policy['resolution_time_minutes'],
                    'working_hours_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
