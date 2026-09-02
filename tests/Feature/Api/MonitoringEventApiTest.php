<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\MonitoringEvent;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringEventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_event_deduplicates_open_event_and_can_auto_create_incident(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-EVT',
            'name' => 'Event Department',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'email' => 'event.supervisor@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        TicketStatus::query()->create(['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_active' => true]);
        TicketPriority::query()->create(['code' => 'P1', 'name' => 'Critical', 'level' => 1, 'is_active' => true]);
        TicketPriority::query()->create(['code' => 'P2', 'name' => 'High', 'level' => 2, 'is_active' => true]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/monitoring-events', [
                'source' => 'Zabbix',
                'severity' => MonitoringEvent::SEVERITY_MEDIUM,
                'message' => 'CPU usage over 80 percent',
                'details' => 'Host app-01 reached warning threshold.',
                'occurred_at' => now()->subMinute()->toISOString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', MonitoringEvent::STATUS_OPEN)
            ->assertJsonPath('data.duplicate_count', 1);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/monitoring-events', [
                'source' => 'Zabbix',
                'severity' => MonitoringEvent::SEVERITY_HIGH,
                'message' => 'CPU usage over 80 percent',
                'details' => 'Host app-01 repeated the same alert.',
                'occurred_at' => now()->toISOString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', MonitoringEvent::STATUS_OPEN)
            ->assertJsonPath('data.duplicate_count', 2)
            ->assertJsonPath('data.severity', MonitoringEvent::SEVERITY_HIGH);

        $this->assertSame(1, MonitoringEvent::query()->where('message', 'CPU usage over 80 percent')->count());

        $criticalResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/monitoring-events', [
                'source' => 'Zabbix',
                'severity' => MonitoringEvent::SEVERITY_CRITICAL,
                'message' => 'Core database unreachable',
                'details' => 'Database health check failed from all probes.',
                'auto_create_incident' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', MonitoringEvent::STATUS_CONVERTED)
            ->assertJsonPath('data.converted_ticket.process_type', Ticket::PROCESS_TYPE_INCIDENT)
            ->assertJsonPath('data.converted_ticket.incident_detection_source', Ticket::DETECTION_SOURCE_MONITORING)
            ->assertJsonPath('data.converted_ticket.impact', Ticket::IMPACT_HIGH)
            ->assertJsonPath('data.converted_ticket.urgency', Ticket::IMPACT_HIGH);

        $this->assertDatabaseHas('monitoring_events', [
            'id' => (int) $criticalResponse->json('data.id'),
            'status' => MonitoringEvent::STATUS_CONVERTED,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => (int) $criticalResponse->json('data.converted_ticket_id'),
            'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
            'incident_detection_source' => Ticket::DETECTION_SOURCE_MONITORING,
            'source' => 'monitoring',
        ]);
    }
}
