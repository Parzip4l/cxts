<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SlaEvent;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Services\SLA\SLATrackingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaEventStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_warning_breach_and_escalation_are_recorded_as_sla_events(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-SLA',
            'name' => 'SLA Department',
            'is_active' => true,
        ]);
        $actor = User::factory()->create([
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);
        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);
        $priority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'is_active' => true,
        ]);
        $status = TicketStatus::query()->create([
            'code' => 'NEW',
            'name' => 'New',
            'is_open' => true,
            'is_active' => true,
        ]);
        $policy = SlaPolicy::query()->create([
            'name' => 'Escalated SLA',
            'response_time_minutes' => 100,
            'resolution_time_minutes' => 200,
            'escalate_on_warning' => true,
            'escalate_on_breach' => true,
            'escalation_role_code' => 'supervisor',
            'is_active' => true,
        ]);

        $createdAt = CarbonImmutable::now()->subMinutes(81);
        $warningTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-SLA-WARN',
            'title' => 'Warning ticket',
            'description' => 'Ticket reaching warning threshold.',
            'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $status->id,
            'sla_policy_id' => $policy->id,
            'sla_name_snapshot' => $policy->name,
            'sla_status' => Ticket::SLA_STATUS_ON_TIME,
            'source' => 'web',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);
        $warningTicket->timestamps = false;
        $warningTicket->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'response_due_at' => $createdAt->addMinutes(100),
            'resolution_due_at' => $createdAt->addMinutes(200),
        ])->save();

        app(SLATrackingService::class)->sync($warningTicket, $actor, CarbonImmutable::now());

        $this->assertDatabaseHas('sla_events', [
            'ticket_id' => $warningTicket->id,
            'sla_policy_id' => $policy->id,
            'event_type' => SlaEvent::TYPE_WARNING,
            'target' => 'response',
            'threshold_percentage' => 80,
        ]);
        $this->assertDatabaseHas('sla_events', [
            'ticket_id' => $warningTicket->id,
            'event_type' => SlaEvent::TYPE_ESCALATION,
            'target' => 'response_warning',
            'escalation_role_code' => 'supervisor',
        ]);

        $breachTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-SLA-BREACH',
            'title' => 'Breach ticket',
            'description' => 'Ticket past response due.',
            'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $status->id,
            'sla_policy_id' => $policy->id,
            'sla_name_snapshot' => $policy->name,
            'response_due_at' => CarbonImmutable::now()->subMinute(),
            'resolution_due_at' => CarbonImmutable::now()->addHour(),
            'sla_status' => Ticket::SLA_STATUS_ON_TIME,
            'source' => 'web',
            'impact' => 'high',
            'urgency' => 'high',
        ]);

        app(SLATrackingService::class)->sync($breachTicket, $actor, CarbonImmutable::now());

        $this->assertDatabaseHas('sla_events', [
            'ticket_id' => $breachTicket->id,
            'event_type' => SlaEvent::TYPE_STATE_CHANGED,
            'target' => 'overall',
            'old_sla_status' => Ticket::SLA_STATUS_ON_TIME,
            'new_sla_status' => Ticket::SLA_STATUS_BREACHED,
        ]);
        $this->assertDatabaseHas('sla_events', [
            'ticket_id' => $breachTicket->id,
            'event_type' => SlaEvent::TYPE_BREACH,
            'target' => 'response',
        ]);
        $this->assertDatabaseHas('sla_events', [
            'ticket_id' => $breachTicket->id,
            'event_type' => SlaEvent::TYPE_ESCALATION,
            'target' => 'response_breach',
            'escalation_role_code' => 'supervisor',
        ]);
    }
}
