<?php

namespace Tests\Feature\Web;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\TicketActivity;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
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

        $firstTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-AST-OPS-001',
            'title' => 'First ticket',
            'description' => 'First ticket',
            'requester_department_id' => $department->id,
            'ticket_status_id' => $completedStatus->id,
            'started_at' => now()->subMinutes(150),
            'completed_at' => now()->subMinutes(30),
            'resolved_at' => now()->subMinutes(30),
        ]);

        $secondTicket = Ticket::query()->create([
            'ticket_number' => 'TCK-AST-OPS-002',
            'title' => 'Second ticket',
            'description' => 'Second ticket',
            'requester_department_id' => $department->id,
            'ticket_status_id' => $completedStatus->id,
            'started_at' => now()->subMinutes(90),
            'completed_at' => now()->subMinutes(10),
            'resolved_at' => now()->subMinutes(10),
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

        $logActivity($firstTicket, 'work_started', now()->subMinutes(150));
        $logActivity($firstTicket, 'work_completed', now()->subMinutes(30));
        $logActivity($secondTicket, 'work_started', now()->subMinutes(90));
        $logActivity($secondTicket, 'work_completed', now()->subMinutes(10));

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'Berapa MTTR saat ini?'])
            ->assertOk()
            ->assertJsonFragment(['suggestions' => ['Berapa ticket overdue saat ini?', 'Berapa ticket unassigned saat ini?', 'Modul apa yang bisa saya akses?']])
            ->assertSee('MTTR final cycle pada periode aktif saat ini adalah 100.00 menit');
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

    public function test_supervisor_can_ask_closed_today_and_average_tickets_this_month(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-19 10:00:00'));

        $department = Department::query()->create([
            'code' => 'DEP-AST-METRIC',
            'name' => 'Assistant Metrics',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $closedStatus = TicketStatus::query()->create([
            'code' => 'CLOSED',
            'name' => 'Closed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-MTR-001',
            'title' => 'Closed today',
            'description' => 'Closed today',
            'requester_department_id' => $department->id,
            'ticket_status_id' => $closedStatus->id,
            'closed_at' => now()->subHour(),
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHour(),
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-MTR-002',
            'title' => 'Created this month',
            'description' => 'Created this month',
            'requester_department_id' => $department->id,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'Berapa Ticket Closed Hari ini ?'])
            ->assertOk()
            ->assertSee('Jumlah ticket closed hari ini adalah 1 ticket.');

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'berapa rata rata tiket bulan ini'])
            ->assertOk()
            ->assertSee('Rata-rata ticket masuk bulan ini adalah');

        CarbonImmutable::setTestNow();
    }

    public function test_supervisor_can_ask_for_top_engineer_effectiveness(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-19 10:00:00'));

        $department = Department::query()->create([
            'code' => 'DEP-AST-ENG',
            'name' => 'Assistant Engineer Metrics',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $engineerTop = User::factory()->create([
            'role' => 'engineer',
            'department_id' => $department->id,
            'name' => 'Engineer Top',
        ]);

        $engineerOther = User::factory()->create([
            'role' => 'engineer',
            'department_id' => $department->id,
            'name' => 'Engineer Other',
        ]);

        $completedStatus = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-ENG-001',
            'title' => 'Top engineer ticket',
            'description' => 'Top engineer ticket',
            'requester_department_id' => $department->id,
            'assigned_engineer_id' => $engineerTop->id,
            'ticket_status_id' => $completedStatus->id,
            'created_at' => now()->subDays(2),
            'responded_at' => now()->subDays(2)->addMinutes(10),
            'response_due_at' => now()->subDays(2)->addHour(),
            'completed_at' => now()->subDays(2)->addHours(2),
            'resolution_due_at' => now()->subDays(2)->addHours(4),
            'updated_at' => now()->subDays(2)->addHours(2),
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-ENG-002',
            'title' => 'Other engineer ticket',
            'description' => 'Other engineer ticket',
            'requester_department_id' => $department->id,
            'assigned_engineer_id' => $engineerOther->id,
            'ticket_status_id' => $completedStatus->id,
            'created_at' => now()->subDays(1),
            'responded_at' => now()->subDays(1)->addHours(2),
            'response_due_at' => now()->subDays(1)->addHour(),
            'completed_at' => now()->subDays(1)->addHours(6),
            'resolution_due_at' => now()->subDays(1)->addHours(4),
            'updated_at' => now()->subDays(1)->addHours(6),
        ]);

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'siapa engineer paling efektif bulan ini'])
            ->assertOk()
            ->assertSee('Engineer paling efektif bulan ini adalah Engineer Top.')
            ->assertSee('Effectiveness score:')
            ->assertSee('Completion rate: 100.00%');

        CarbonImmutable::setTestNow();
    }

    public function test_supervisor_can_ask_for_engineer_close_workload_and_sla_rankings(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-19 10:00:00'));

        $department = Department::query()->create([
            'code' => 'DEP-AST-ENG-RANK',
            'name' => 'Assistant Engineer Rankings',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $engineerCloser = User::factory()->create([
            'role' => 'engineer',
            'department_id' => $department->id,
            'name' => 'Engineer Closer',
        ]);

        $engineerBusy = User::factory()->create([
            'role' => 'engineer',
            'department_id' => $department->id,
            'name' => 'Engineer Busy',
        ]);

        $completedStatus = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-ENG-RANK-001',
            'title' => 'Closed by closer',
            'description' => 'Closed by closer',
            'requester_department_id' => $department->id,
            'assigned_engineer_id' => $engineerCloser->id,
            'ticket_status_id' => $completedStatus->id,
            'created_at' => now()->subDays(2),
            'responded_at' => now()->subDays(2)->addMinutes(15),
            'response_due_at' => now()->subDays(2)->addHour(),
            'completed_at' => now()->subDays(2)->addHours(2),
            'resolution_due_at' => now()->subDays(2)->addHours(4),
            'updated_at' => now()->subDays(2)->addHours(2),
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-AST-ENG-RANK-002',
            'title' => 'Open busy ticket',
            'description' => 'Open busy ticket',
            'requester_department_id' => $department->id,
            'assigned_engineer_id' => $engineerBusy->id,
            'created_at' => now()->subDay(),
            'response_due_at' => now()->addHour(),
            'resolution_due_at' => now()->addHours(8),
            'updated_at' => now()->subDay(),
        ]);

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'siapa engineer paling banyak close ticket bulan ini'])
            ->assertOk()
            ->assertSee('Engineer Closer')
            ->assertSee('Completed tickets: 1.00');

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'siapa engineer workload tertinggi bulan ini'])
            ->assertOk()
            ->assertSee('Engineer dengan workload tertinggi bulan ini adalah Engineer Busy.');

        $this->actingAs($supervisor)
            ->postJson(route('assistant.respond'), ['message' => 'siapa engineer dengan SLA terbaik bulan ini'])
            ->assertOk()
            ->assertSee('Engineer dengan SLA terbaik bulan ini adalah Engineer Closer.');

        CarbonImmutable::setTestNow();
    }

    public function test_operational_admin_can_ask_for_asset_summaries(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-AST-ASSET',
            'name' => 'Assistant Asset Metrics',
            'is_active' => true,
        ]);

        $operationalAdmin = User::factory()->create([
            'role' => 'operational_admin',
            'department_id' => $department->id,
        ]);

        $assetCategory = AssetCategory::query()->create([
            'code' => 'CAT-AST-001',
            'name' => 'Network Device',
            'is_active' => true,
        ]);

        $operationalStatus = AssetStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_operational' => true,
            'is_active' => true,
        ]);

        $repairStatus = AssetStatus::query()->create([
            'code' => 'REPAIR',
            'name' => 'Under Repair',
            'is_operational' => false,
            'is_active' => true,
        ]);

        $locationA = AssetLocation::query()->create([
            'code' => 'LOC-AST-001',
            'name' => 'Main Hub',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $locationB = AssetLocation::query()->create([
            'code' => 'LOC-AST-002',
            'name' => 'Backup Hub',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-001',
            'name' => 'Core Switch A',
            'asset_category_id' => $assetCategory->id,
            'asset_location_id' => $locationA->id,
            'asset_status_id' => $operationalStatus->id,
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-002',
            'name' => 'Core Switch B',
            'asset_category_id' => $assetCategory->id,
            'asset_location_id' => $locationA->id,
            'asset_status_id' => $operationalStatus->id,
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-003',
            'name' => 'UPS C',
            'asset_category_id' => $assetCategory->id,
            'asset_location_id' => $locationB->id,
            'asset_status_id' => $repairStatus->id,
            'is_active' => false,
        ]);

        $this->actingAs($operationalAdmin)
            ->postJson(route('assistant.respond'), ['message' => 'berapa total asset aktif'])
            ->assertOk()
            ->assertSee('Ringkasan asset saat ini:')
            ->assertSee('Asset aktif: 2');

        $this->actingAs($operationalAdmin)
            ->postJson(route('assistant.respond'), ['message' => 'status asset apa yang paling banyak dipakai'])
            ->assertOk()
            ->assertSee('Status asset yang paling banyak dipakai saat ini adalah Active.');

        $this->actingAs($operationalAdmin)
            ->postJson(route('assistant.respond'), ['message' => 'berapa total asset location'])
            ->assertOk()
            ->assertSee('Jumlah master asset location aktif saat ini adalah 2 lokasi.');
    }
}
