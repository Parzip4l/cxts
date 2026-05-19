<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_gets_only_own_ticket_summary(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-AST-REQ',
            'name' => 'Assistant Requester',
            'is_active' => true,
        ]);

        $requester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $otherRequester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $completedStatus = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-REQ-001',
            'title' => 'Requester open ticket',
            'description' => 'Open ticket',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-REQ-002',
            'title' => 'Requester completed ticket',
            'description' => 'Completed ticket',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_status_id' => $completedStatus->id,
            'completed_at' => now(),
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-REQ-003',
            'title' => 'Other requester ticket',
            'description' => 'Other ticket',
            'requester_id' => $otherRequester->id,
            'requester_department_id' => $department->id,
        ]);

        $this->actingAs($requester)
            ->postJson(route('assistant.respond'), ['message' => 'Tiket saya'])
            ->assertOk()
            ->assertJsonPath('suggestions.0', 'Status ticket saya terbaru')
            ->assertJsonFragment(['message' => "Ringkasan ticket yang bisa Anda akses:\n- Total ticket: 2\n- Masih terbuka: 1\n- Sudah completed: 1\n- Ticket terbaru: TCK-AST-REQ-002 - Requester completed ticket"]);
    }

    public function test_supervisor_can_ask_for_mttr(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-AST-OPS',
            'name' => 'Assistant Ops',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $completedStatus = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-OPS-001',
            'title' => 'First ticket',
            'description' => 'First ticket',
            'requester_department_id' => $department->id,
            'ticket_status_id' => $completedStatus->id,
            'started_at' => now()->subMinutes(150),
            'completed_at' => now()->subMinutes(30),
            'resolved_at' => now()->subMinutes(30),
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-OPS-002',
            'title' => 'Second ticket',
            'description' => 'Second ticket',
            'requester_department_id' => $department->id,
            'ticket_status_id' => $completedStatus->id,
            'started_at' => now()->subMinutes(90),
            'completed_at' => now()->subMinutes(10),
            'resolved_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'Berapa MTTR saat ini?'])
            ->assertOk()
            ->assertJsonFragment(['suggestions' => ['Berapa ticket overdue saat ini?', 'Berapa ticket unassigned saat ini?', 'Modul apa yang bisa saya akses?']])
            ->assertSee('MTTR pada periode aktif saat ini adalah 100.00 menit');
    }

    public function test_assistant_rejects_out_of_scope_question(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
        ]);

        $this->actingAs($user)
            ->postJson(route('assistant.respond'), ['message' => 'Siapa presiden Indonesia?'])
            ->assertOk()
            ->assertSee('Saya hanya menjawab hal yang terkait CXTS');
    }
}
