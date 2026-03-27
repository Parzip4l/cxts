<?php

namespace Database\Seeders;

use App\Models\ServiceCatalog;
use App\Models\SlaPolicy;
use App\Models\SlaPolicyAssignment;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketSubcategory;
use Illuminate\Database\Seeder;

class SlaPolicyAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $incident = TicketCategory::query()->where('code', 'INCIDENT')->first();
        $request = TicketCategory::query()->where('code', 'REQUEST')->first();
        $maintenance = TicketCategory::query()->where('code', 'MAINTENANCE')->first();
        $networkDown = TicketSubcategory::query()->where('code', 'NETWORK_DOWN')->first();
        $performance = TicketSubcategory::query()->where('code', 'PERFORMANCE')->first();
        $wirelessOutage = TicketDetailSubcategory::query()->where('code', 'WIRELESS_OUTAGE')->first();
        $highLatency = TicketDetailSubcategory::query()->where('code', 'HIGH_LATENCY')->first();
        $priorityP1 = TicketPriority::query()->where('code', 'P1')->first();
        $wifiCore = ServiceCatalog::query()->where('code', 'SRV-WIFI-CORE')->first();

        $assignments = [
            [
                'policy' => 'NETWORK_OUTAGE_CRITICAL',
                'ticket_type' => 'incident',
                'category_id' => $incident?->id,
                'subcategory_id' => $networkDown?->id,
                'detail_subcategory_id' => $wirelessOutage?->id,
                'service_item_id' => $wifiCore?->id,
                'priority_id' => $priorityP1?->id,
                'impact' => 'high',
                'urgency' => 'high',
                'sort_order' => 1,
            ],
            [
                'policy' => 'PERFORMANCE_DEFAULT',
                'ticket_type' => null,
                'category_id' => null,
                'subcategory_id' => $performance?->id,
                'detail_subcategory_id' => $highLatency?->id,
                'service_item_id' => null,
                'priority_id' => null,
                'impact' => null,
                'urgency' => null,
                'sort_order' => 10,
            ],
            [
                'policy' => 'STANDARD_24X7',
                'ticket_type' => null,
                'category_id' => $incident?->id,
                'subcategory_id' => null,
                'detail_subcategory_id' => null,
                'service_item_id' => null,
                'priority_id' => null,
                'impact' => null,
                'urgency' => null,
                'sort_order' => 100,
            ],
            [
                'policy' => 'REQUEST_STANDARD',
                'ticket_type' => null,
                'category_id' => $request?->id,
                'subcategory_id' => null,
                'detail_subcategory_id' => null,
                'service_item_id' => null,
                'priority_id' => null,
                'impact' => null,
                'urgency' => null,
                'sort_order' => 100,
            ],
            [
                'policy' => 'CHANGE_STANDARD',
                'ticket_type' => null,
                'category_id' => $maintenance?->id,
                'subcategory_id' => null,
                'detail_subcategory_id' => null,
                'service_item_id' => null,
                'priority_id' => null,
                'impact' => null,
                'urgency' => null,
                'sort_order' => 100,
            ],
        ];

        foreach ($assignments as $assignment) {
            $policy = SlaPolicy::query()->where('name', $assignment['policy'])->first();
            if ($policy === null) {
                continue;
            }

            SlaPolicyAssignment::query()->updateOrCreate(
                [
                    'sla_policy_id' => $policy->id,
                    'ticket_type' => $assignment['ticket_type'],
                    'category_id' => $assignment['category_id'],
                    'subcategory_id' => $assignment['subcategory_id'],
                    'detail_subcategory_id' => $assignment['detail_subcategory_id'],
                    'service_item_id' => $assignment['service_item_id'],
                    'priority_id' => $assignment['priority_id'],
                    'impact' => $assignment['impact'],
                    'urgency' => $assignment['urgency'],
                ],
                [
                    'sort_order' => $assignment['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
