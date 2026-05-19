<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\TicketActivity;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketWorklog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_access_dashboard_overview_and_engineer_effectiveness(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-26 09:00:00'));

        $department = Department::query()->create([
            'code' => 'DEP-DASH',
            'name' => 'Dashboard Ops',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'email' => 'supervisor.dashboard@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $engineerOne = User::factory()->create([
            'email' => 'engineer.one.dashboard@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $engineerTwo = User::factory()->create([
            'email' => 'engineer.two.dashboard@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $statusAssigned = TicketStatus::query()->create([
            'code' => 'ASSIGNED',
            'name' => 'Assigned',
            'is_open' => true,
            'is_active' => true,
        ]);

        $statusCompleted = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        $statusCancelled = TicketStatus::query()->updateOrCreate(
            ['code' => 'CANCELLED'],
            [
                'name' => 'Cancelled',
                'is_closed' => true,
                'is_active' => true,
            ]
        );

        $ticketOnTime = Ticket::query()->create([
            'ticket_number' => 'TCK-DASH-0001',
            'title' => 'Issue A',
            'description' => 'Issue A description',
            'ticket_status_id' => $statusCompleted->id,
            'assigned_engineer_id' => $engineerOne->id,
            'response_due_at' => now()->addMinutes(30),
            'resolution_due_at' => now()->addHours(3),
            'started_at' => now()->addMinutes(20),
            'completed_at' => now()->addHours(2),
            'source' => 'web',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $ticketBreach = Ticket::query()->create([
            'ticket_number' => 'TCK-DASH-0002',
            'title' => 'Issue B',
            'description' => 'Issue B description',
            'ticket_status_id' => $statusCompleted->id,
            'assigned_engineer_id' => $engineerOne->id,
            'response_due_at' => now()->addMinutes(30),
            'resolution_due_at' => now()->addHours(2),
            'started_at' => now()->addMinutes(50),
            'completed_at' => now()->addHours(3),
            'source' => 'web',
            'impact' => 'high',
            'urgency' => 'high',
        ]);

        $ticketPending = Ticket::query()->create([
            'ticket_number' => 'TCK-DASH-0003',
            'title' => 'Issue C',
            'description' => 'Issue C description',
            'ticket_status_id' => $statusAssigned->id,
            'assigned_engineer_id' => $engineerTwo->id,
            'response_due_at' => now()->subMinutes(10),
            'resolution_due_at' => now()->addHours(2),
            'started_at' => null,
            'completed_at' => null,
            'source' => 'api',
            'impact' => 'low',
            'urgency' => 'low',
        ]);

        $ticketReopened = Ticket::query()->create([
            'ticket_number' => 'TCK-DASH-0005',
            'title' => 'Issue Reopened',
            'description' => 'Issue reopened description',
            'ticket_status_id' => $statusCompleted->id,
            'assigned_engineer_id' => $engineerTwo->id,
            'response_due_at' => now()->addMinutes(30),
            'resolution_due_at' => now()->addHours(4),
            'started_at' => now()->subMinutes(40),
            'completed_at' => now()->subMinutes(10),
            'resolved_at' => now()->subMinutes(10),
            'source' => 'web',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-DASH-0004',
            'title' => 'Issue D',
            'description' => 'Issue D description',
            'ticket_status_id' => $statusCancelled->id,
            'assigned_engineer_id' => $engineerTwo->id,
            'response_due_at' => now()->addMinutes(15),
            'resolution_due_at' => now()->addHour(),
            'source' => 'web',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        TicketWorklog::query()->create([
            'ticket_id' => $ticketOnTime->id,
            'user_id' => $engineerOne->id,
            'log_type' => 'progress',
            'description' => 'Worklog one',
            'duration_minutes' => 90,
        ]);

        TicketWorklog::query()->create([
            'ticket_id' => $ticketPending->id,
            'user_id' => $engineerTwo->id,
            'log_type' => 'progress',
            'description' => 'Worklog two',
            'duration_minutes' => 35,
        ]);

        $logActivity = function (Ticket $ticket, string $type, $at): void {
            $activity = TicketActivity::query()->create([
                'ticket_id' => $ticket->id,
                'activity_type' => $type,
            ]);

            $activity->timestamps = false;
            $activity->forceFill([
                'created_at' => $at,
                'updated_at' => $at,
            ])->save();
        };

        $logActivity($ticketOnTime, 'work_started', now()->addMinutes(20));
        $logActivity($ticketOnTime, 'work_completed', now()->addHours(2));

        $logActivity($ticketBreach, 'work_started', now()->addMinutes(50));
        $logActivity($ticketBreach, 'work_completed', now()->addHours(3));

        $logActivity($ticketReopened, 'work_started', now()->subHours(5));
        $logActivity($ticketReopened, 'work_completed', now()->subHours(4));
        $logActivity($ticketReopened, 'ticket_reopened', now()->subHours(2));
        $logActivity($ticketReopened, 'work_started', now()->subMinutes(40));
        $logActivity($ticketReopened, 'work_completed', now()->subMinutes(10));

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $query = '?date_from='.now()->subDay()->toDateString().'&date_to='.now()->addDay()->toDateString();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/dashboard/overview'.$query)
            ->assertOk()
            ->assertJsonPath('summary.total_tickets', 5)
            ->assertJsonPath('summary.mttr_minutes', 86.67)
            ->assertJsonPath('summary.mttr_ticket_count', 3)
            ->assertJsonPath('summary.reopened_ticket_count', 1)
            ->assertJsonPath('summary.reopen_rate', 33.33)
            ->assertJsonPath('sla.response.breached', 2)
            ->assertJsonPath('sla.resolution.breached', 1);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/dashboard/engineer-effectiveness'.$query)
            ->assertOk()
            ->assertJsonPath('summary.engineer_count', 2)
            ->assertJsonPath('engineers.0.engineer_name', $engineerOne->name)
            ->assertJsonPath('engineers.0.assigned_tickets', 2)
            ->assertJsonPath('summary.total_assigned_tickets', 4);

        CarbonImmutable::setTestNow();
    }

    public function test_engineer_can_access_own_performance_but_not_ops_dashboard_endpoint(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-ENG-PERF',
            'name' => 'Engineering Performance',
            'is_active' => true,
        ]);

        $engineer = User::factory()->create([
            'email' => 'engineer.performance@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $statusAssigned = TicketStatus::query()->create([
            'code' => 'ASSIGNED',
            'name' => 'Assigned',
            'is_open' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-ENG-0001',
            'title' => 'Issue Engineering',
            'description' => 'Issue engineering description',
            'ticket_status_id' => $statusAssigned->id,
            'assigned_engineer_id' => $engineer->id,
            'response_due_at' => now()->addHour(),
            'resolution_due_at' => now()->addHours(4),
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/engineer/performance')
            ->assertOk()
            ->assertJsonPath('engineer.engineer_id', $engineer->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/dashboard/overview')
            ->assertForbidden();
    }
}
