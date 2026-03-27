<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineerTaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_can_run_task_lifecycle_via_api(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-ENG',
            'name' => 'Engineering',
            'is_active' => true,
        ]);

        $engineer = User::factory()->create([
            'email' => 'engineer.task@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'requester.task@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        $subcategory = TicketSubcategory::query()->create([
            'ticket_category_id' => $category->id,
            'code' => 'PERFORMANCE',
            'name' => 'Performance',
            'is_active' => true,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P3',
            'name' => 'Medium',
            'level' => 3,
            'response_target_minutes' => 60,
            'resolution_target_minutes' => 480,
            'is_active' => true,
        ]);

        $assigned = TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);
        $inProgress = TicketStatus::query()->create(['code' => 'IN_PROGRESS', 'name' => 'In Progress', 'is_open' => true, 'is_in_progress' => true, 'is_active' => true]);
        $onHold = TicketStatus::query()->create(['code' => 'ON_HOLD', 'name' => 'On Hold', 'is_open' => true, 'is_active' => true]);
        $completed = TicketStatus::query()->create(['code' => 'COMPLETED', 'name' => 'Completed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'CLOSED', 'name' => 'Closed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-TEST-0001',
            'title' => 'Cek performa jaringan antar lantai',
            'description' => 'Latency tinggi di jam sibuk.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_subcategory_id' => $subcategory->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $assigned->id,
            'assigned_engineer_id' => $engineer->id,
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/engineer/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.id', $ticket->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/start', [
                'notes' => 'Mulai investigasi issue jaringan.',
            ])
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $inProgress->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/pause', [
                'notes' => 'Menunggu akses ruang server.',
            ])
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $onHold->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/resume', [
                'notes' => 'Akses sudah diberikan, lanjut pemeriksaan.',
            ])
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $inProgress->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/worklogs', [
                'log_type' => 'progress',
                'description' => 'Cek uplink dan load interface switch distribusi.',
                'started_at' => now()->subMinutes(40)->toIso8601String(),
                'ended_at' => now()->subMinutes(10)->toIso8601String(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.ticket_id', $ticket->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/complete', [
                'notes' => 'Issue selesai setelah optimasi konfigurasi QoS.',
            ])
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $completed->id);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'ticket_status_id' => $completed->id,
        ]);

        $this->assertDatabaseHas('ticket_worklogs', [
            'ticket_id' => $ticket->id,
            'user_id' => $engineer->id,
        ]);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'activity_type' => 'work_completed',
        ]);
    }

    public function test_engineer_cannot_start_twice_or_transition_after_task_completed(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-ENG-02',
            'name' => 'Engineering 02',
            'is_active' => true,
        ]);

        $engineer = User::factory()->create([
            'email' => 'engineer.guard@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'requester.guard@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'REQ',
            'name' => 'Request',
            'is_active' => true,
        ]);

        $subcategory = TicketSubcategory::query()->create([
            'ticket_category_id' => $category->id,
            'code' => 'INSTALL',
            'name' => 'Install',
            'is_active' => true,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'response_target_minutes' => 30,
            'resolution_target_minutes' => 180,
            'is_active' => true,
        ]);

        $assigned = TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);
        $inProgress = TicketStatus::query()->create(['code' => 'IN_PROGRESS', 'name' => 'In Progress', 'is_open' => true, 'is_in_progress' => true, 'is_active' => true]);
        $completed = TicketStatus::query()->create(['code' => 'COMPLETED', 'name' => 'Completed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'ON_HOLD', 'name' => 'On Hold', 'is_open' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'CLOSED', 'name' => 'Closed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-GUARD-0001',
            'title' => 'Testing guard transition',
            'description' => 'Validasi aksi transisi task engineer.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_subcategory_id' => $subcategory->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $assigned->id,
            'assigned_engineer_id' => $engineer->id,
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'high',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/start')
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $inProgress->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/start')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['action']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.ticket_status_id', $completed->id);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer/tasks/'.$ticket->id.'/pause')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['action']);
    }
}
