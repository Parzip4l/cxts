<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAssignment;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketWorklog;
use App\Models\User;
use App\Services\SLA\SLAResolverService;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::query()->where('email', 'requester@demo.com')->first();
        $supervisor = User::query()->where('email', 'supervisor@demo.com')->first();
        $engineer = User::query()->where('email', 'engineer1@demo.com')->first();

        $incidentCategory = TicketCategory::query()->where('code', 'INCIDENT')->first();
        $networkDownSubcategory = TicketSubcategory::query()->where('code', 'NETWORK_DOWN')->first();
        $performanceSubcategory = TicketSubcategory::query()->where('code', 'PERFORMANCE')->first();
        $lanOutageDetail = TicketDetailSubcategory::query()->where('code', 'LAN_OUTAGE')->first();
        $highLatencyDetail = TicketDetailSubcategory::query()->where('code', 'HIGH_LATENCY')->first();

        $priorityHigh = TicketPriority::query()->where('code', 'P2')->first();
        $priorityMedium = TicketPriority::query()->where('code', 'P3')->first();

        $statusNew = TicketStatus::query()->where('code', 'NEW')->first();
        $statusInProgress = TicketStatus::query()->where('code', 'IN_PROGRESS')->first();

        $service = ServiceCatalog::query()->where('code', 'SRV-WIFI-CORE')->first();
        $asset = Asset::query()->where('code', 'AST-AP-001')->first();
        $location = AssetLocation::query()->where('code', 'LOC-JKT-001')->first();
        $slaResolver = app(SLAResolverService::class);

        if ($incidentCategory === null || $priorityHigh === null || $statusNew === null) {
            return;
        }

        $ticket1CreatedAt = now();
        $ticket1Sla = $slaResolver->resolveSLA([
            'category_id' => $incidentCategory->id,
            'subcategory_id' => $networkDownSubcategory?->id,
            'service_item_id' => $service?->id,
            'priority_id' => $priorityHigh->id,
            'impact' => 'high',
            'urgency' => 'high',
        ]);

        $ticket1 = Ticket::query()->updateOrCreate(
            ['ticket_number' => 'TCK-SEED-0001'],
            [
                'title' => 'WiFi access point di lobby tidak bisa konek',
                'description' => 'User tidak dapat internet dari access point lobby lantai 1 sejak pagi.',
                'requester_id' => $requester?->id,
                'requester_department_id' => $requester?->department_id,
                'ticket_category_id' => $incidentCategory->id,
                'ticket_subcategory_id' => $networkDownSubcategory?->id,
                'ticket_detail_subcategory_id' => $lanOutageDetail?->id,
                'ticket_priority_id' => $priorityHigh->id,
                'service_id' => $service?->id,
                'asset_id' => $asset?->id,
                'asset_location_id' => $location?->id,
                'ticket_status_id' => $statusNew->id,
                'source' => 'web',
                'impact' => 'high',
                'urgency' => 'high',
                'sla_policy_id' => $ticket1Sla->policyId,
                'sla_policy_name' => $ticket1Sla->name,
                'sla_name_snapshot' => $ticket1Sla->name,
                'response_due_at' => $ticket1Sla->responseDueAt(\Carbon\CarbonImmutable::instance($ticket1CreatedAt)),
                'resolution_due_at' => $ticket1Sla->resolutionDueAt(\Carbon\CarbonImmutable::instance($ticket1CreatedAt)),
                'sla_status' => $ticket1Sla->hasTargets() ? Ticket::SLA_STATUS_ON_TIME : null,
                'created_by_id' => $requester?->id,
                'updated_by_id' => $requester?->id,
                'last_status_changed_at' => $ticket1CreatedAt,
            ]
        );

        TicketActivity::query()->updateOrCreate(
            ['ticket_id' => $ticket1->id, 'activity_type' => 'ticket_created'],
            [
                'actor_user_id' => $requester?->id,
                'old_status_id' => null,
                'new_status_id' => $statusNew->id,
                'metadata' => ['source' => 'seeder'],
            ]
        );

        if ($priorityMedium === null || $statusInProgress === null || $engineer === null) {
            return;
        }

        $ticket2CreatedAt = now()->subMinutes(95);
        $ticket2StartedAt = now()->subMinutes(90);
        $ticket2Sla = $slaResolver->resolveSLA([
            'category_id' => $incidentCategory->id,
            'subcategory_id' => $performanceSubcategory?->id,
            'service_item_id' => $service?->id,
            'priority_id' => $priorityMedium->id,
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $ticket2 = Ticket::query()->updateOrCreate(
            ['ticket_number' => 'TCK-SEED-0002'],
            [
                'title' => 'Throughput WiFi menurun signifikan di area kantor utama',
                'description' => 'Performa internet melambat saat jam operasional, perlu pengecekan AP dan uplink switch.',
                'requester_id' => $requester?->id,
                'requester_department_id' => $requester?->department_id,
                'ticket_category_id' => $incidentCategory->id,
                'ticket_subcategory_id' => $performanceSubcategory?->id,
                'ticket_detail_subcategory_id' => $highLatencyDetail?->id,
                'ticket_priority_id' => $priorityMedium->id,
                'service_id' => $service?->id,
                'asset_id' => $asset?->id,
                'asset_location_id' => $location?->id,
                'ticket_status_id' => $statusInProgress->id,
                'assigned_team_name' => 'Field Engineering',
                'assigned_engineer_id' => $engineer->id,
                'source' => 'web',
                'impact' => 'medium',
                'urgency' => 'medium',
                'sla_policy_id' => $ticket2Sla->policyId,
                'sla_policy_name' => $ticket2Sla->name,
                'sla_name_snapshot' => $ticket2Sla->name,
                'started_at' => $ticket2StartedAt,
                'responded_at' => $ticket2StartedAt,
                'response_due_at' => $ticket2Sla->responseDueAt(\Carbon\CarbonImmutable::instance($ticket2CreatedAt)),
                'resolution_due_at' => $ticket2Sla->resolutionDueAt(\Carbon\CarbonImmutable::instance($ticket2CreatedAt)),
                'sla_status' => Ticket::SLA_STATUS_BREACHED,
                'breached_response_at' => $ticket2Sla->responseDueAt(\Carbon\CarbonImmutable::instance($ticket2CreatedAt)),
                'created_by_id' => $requester?->id,
                'updated_by_id' => $supervisor?->id,
                'last_status_changed_at' => $ticket2StartedAt,
                'created_at' => $ticket2CreatedAt,
                'updated_at' => $ticket2StartedAt,
            ]
        );

        TicketAssignment::query()->updateOrCreate(
            ['ticket_id' => $ticket2->id],
            [
                'previous_engineer_id' => null,
                'assigned_engineer_id' => $engineer->id,
                'assigned_by_id' => $supervisor?->id,
                'assigned_at' => now()->subMinutes(95),
                'notes' => 'Prioritaskan pengecekan uplink switch dan AP utama.',
            ]
        );

        TicketWorklog::query()->updateOrCreate(
            ['ticket_id' => $ticket2->id, 'description' => 'Mulai diagnosis AP lobby dan cek performa uplink switch.'],
            [
                'user_id' => $engineer->id,
                'log_type' => 'progress',
                'started_at' => now()->subMinutes(90),
                'ended_at' => now()->subMinutes(60),
                'duration_minutes' => 30,
            ]
        );

        TicketActivity::query()->updateOrCreate(
            ['ticket_id' => $ticket2->id, 'activity_type' => 'ticket_assigned'],
            [
                'actor_user_id' => $supervisor?->id,
                'old_status_id' => $statusNew->id,
                'new_status_id' => $statusInProgress->id,
                'metadata' => [
                    'assigned_engineer_id' => $engineer->id,
                    'assigned_team_name' => 'Field Engineering',
                    'source' => 'seeder',
                ],
            ]
        );

        TicketActivity::query()->updateOrCreate(
            ['ticket_id' => $ticket2->id, 'activity_type' => 'work_started'],
            [
                'actor_user_id' => $engineer->id,
                'old_status_id' => $statusInProgress->id,
                'new_status_id' => $statusInProgress->id,
                'metadata' => ['notes' => 'Start diagnosis dari mobile engineer'],
            ]
        );
    }
}
