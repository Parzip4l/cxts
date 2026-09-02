<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProblemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_create_problem_and_link_incidents(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-PRB',
            'name' => 'Problem Department',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'email' => 'problem.supervisor@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $owner = User::factory()->create([
            'email' => 'problem.owner@example.com',
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'is_active' => true,
        ]);

        $status = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-PRB-0001',
            'title' => 'Recurring WiFi drop',
            'description' => 'WiFi drops every Monday.',
            'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
            'requester_department_id' => $department->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $status->id,
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/problems', [
                'title' => 'Recurring WiFi instability',
                'description' => 'Multiple related incidents indicate recurring instability.',
                'status' => Problem::STATUS_INVESTIGATING,
                'owner_user_id' => $owner->id,
                'ticket_priority_id' => $priority->id,
                'symptom' => 'WiFi connection drops repeatedly.',
                'root_cause' => 'Controller firmware bug suspected.',
                'workaround' => 'Restart affected access point group.',
                'permanent_fix' => 'Upgrade controller firmware.',
                'is_known_error' => true,
                'action_item' => 'Schedule firmware upgrade.',
                'ticket_ids' => [$ticket->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Recurring WiFi instability')
            ->assertJsonPath('data.is_known_error', true)
            ->assertJsonPath('data.ticket_count', 1)
            ->assertJsonPath('data.tickets.0.id', $ticket->id);

        $this->assertDatabaseHas('problems', [
            'id' => (int) $response->json('data.id'),
            'status' => Problem::STATUS_INVESTIGATING,
            'is_known_error' => true,
        ]);

        $this->assertDatabaseHas('problem_ticket', [
            'problem_id' => (int) $response->json('data.id'),
            'ticket_id' => $ticket->id,
        ]);
    }
}
