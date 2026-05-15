<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketEngineerAssignment;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_close_reopen_and_cancel_ticket(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-LIFE',
            'name' => 'Lifecycle Department',
            'is_active' => true,
        ]);

        $actor = User::factory()->create([
            'email' => 'lifecycle.actor@example.com',
            'role' => 'super_admin',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'lifecycle.requester@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $engineer = User::factory()->create([
            'email' => 'lifecycle.engineer@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        $subcategory = TicketSubcategory::query()->create([
            'ticket_category_id' => $category->id,
            'code' => 'LIFE-SUB',
            'name' => 'Lifecycle Subcategory',
            'is_active' => true,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'response_target_minutes' => 30,
            'resolution_target_minutes' => 240,
            'is_active' => true,
        ]);

        TicketStatus::query()->create(['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_active' => true]);
        $assigned = TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);
        $completed = TicketStatus::query()->create(['code' => 'COMPLETED', 'name' => 'Completed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);
        $closed = TicketStatus::query()->create(['code' => 'CLOSED', 'name' => 'Closed', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);
        $cancelled = TicketStatus::query()->updateOrCreate(['code' => 'CANCELLED'], ['name' => 'Cancelled', 'is_open' => false, 'is_closed' => true, 'is_active' => true]);
        TicketStatus::query()->updateOrCreate(['code' => 'PENDING_CUSTOMER'], ['name' => 'Pending Customer', 'is_open' => true, 'is_active' => true]);

        $completedTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-LIFE-0001',
            'title' => 'Completed ticket ready to close',
            'description' => 'Lifecycle service verification for close and reopen.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_subcategory_id' => $subcategory->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $completed->id,
            'assigned_engineer_id' => $engineer->id,
            'source' => 'test',
            'impact' => 'medium',
            'urgency' => 'medium',
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
            'resolved_at' => now()->subHour(),
        ]);

        $assignedTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-LIFE-0002',
            'title' => 'Assigned ticket ready to cancel',
            'description' => 'Lifecycle service verification for cancel.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_subcategory_id' => $subcategory->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $assigned->id,
            'assigned_engineer_id' => $engineer->id,
            'source' => 'test',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $service = app(TicketService::class);

        $closedTicket = $service->close($completedTicket, $actor, 'Requester menerima hasil pekerjaan.');
        $this->assertSame($closed->id, $closedTicket->ticket_status_id);
        $this->assertNotNull($closedTicket->closed_at);

        $reopenedTicket = $service->reopen($closedTicket, $actor, 'Masalah muncul kembali dan perlu ditangani ulang.');
        $this->assertSame($assigned->id, $reopenedTicket->ticket_status_id);
        $this->assertNull($reopenedTicket->started_at);
        $this->assertNull($reopenedTicket->completed_at);
        $this->assertNull($reopenedTicket->resolved_at);
        $this->assertNull($reopenedTicket->closed_at);

        $cancelledTicket = $service->cancel($assignedTicket, $actor, 'Ticket duplikat, dibatalkan oleh koordinator.');
        $this->assertSame($cancelled->id, $cancelledTicket->ticket_status_id);
        $this->assertNotNull($cancelledTicket->closed_at);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $completedTicket->id,
            'activity_type' => 'ticket_closed',
        ]);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $completedTicket->id,
            'activity_type' => 'ticket_reopened',
        ]);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $assignedTicket->id,
            'activity_type' => 'ticket_cancelled',
        ]);
    }

    public function test_service_can_assign_multiple_engineers_with_equal_score_share(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-MULTI-ASSIGN',
            'name' => 'Multi Assign Department',
            'is_active' => true,
        ]);

        $actor = User::factory()->create([
            'email' => 'multi.assign.actor@example.com',
            'role' => 'super_admin',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'multi.assign.requester@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $engineerOne = User::factory()->create([
            'email' => 'multi.assign.engineer.one@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $engineerTwo = User::factory()->create([
            'email' => 'multi.assign.engineer.two@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $new = TicketStatus::query()->create(['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_active' => true]);
        $assigned = TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-MULTI-0001',
            'title' => 'Ticket handled by two engineers',
            'description' => 'Verify equal scoring share for multiple engineers.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_status_id' => $new->id,
            'requires_approval' => false,
            'allow_direct_assignment' => true,
            'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
            'source' => 'test',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $updated = app(TicketService::class)->assign(
            $ticket,
            collect([$engineerOne, $engineerTwo]),
            $actor,
            'Joint Field Team',
            'Handle together.'
        );

        $this->assertSame($assigned->id, $updated->ticket_status_id);
        $this->assertSame($engineerOne->id, $updated->assigned_engineer_id);
        $this->assertTrue($updated->isAssignedTo($engineerOne));
        $this->assertTrue($updated->isAssignedTo($engineerTwo));

        $assignments = TicketEngineerAssignment::query()
            ->where('ticket_id', $ticket->id)
            ->orderBy('engineer_id')
            ->get();

        $this->assertCount(2, $assignments);
        $this->assertEquals(0.5, (float) $assignments[0]->score_share);
        $this->assertEquals(0.5, (float) $assignments[1]->score_share);
    }

    public function test_service_can_update_ticket_details_without_cancelling_ticket(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-TICKET-EDIT',
            'name' => 'Ticket Edit Department',
            'is_active' => true,
        ]);

        $actor = User::factory()->create([
            'email' => 'ticket.edit.actor@example.com',
            'role' => 'super_admin',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'ticket.edit.requester@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $assigned = TicketStatus::query()->create([
            'code' => 'ASSIGNED',
            'name' => 'Assigned',
            'is_open' => true,
            'is_active' => true,
        ]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-EDIT-0001',
            'title' => 'Old title',
            'description' => 'Old description',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_status_id' => $assigned->id,
            'source' => 'test',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $updatedTicket = app(TicketService::class)->updateDetails($ticket, [
            'title' => 'Updated title',
            'description' => 'Updated description',
        ], $actor);

        $this->assertSame('Updated title', $updatedTicket->title);
        $this->assertSame('Updated description', $updatedTicket->description);
        $this->assertSame($assigned->id, $updatedTicket->ticket_status_id);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'activity_type' => 'ticket_updated',
        ]);
    }
}
