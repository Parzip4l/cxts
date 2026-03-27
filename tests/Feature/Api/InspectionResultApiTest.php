<?php

namespace Tests\Feature\Api;

use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionResultApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_access_complete_inspection_results_and_summary(): void
    {
        $supervisor = User::factory()->create([
            'email' => 'supervisor.results@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
        ]);

        $officerOne = User::factory()->create([
            'email' => 'officer.one.results@example.com',
            'role' => 'inspection_officer',
        ]);

        $officerTwo = User::factory()->create([
            'email' => 'officer.two.results@example.com',
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-RESULT-API',
            'name' => 'Inspection Result API',
            'is_active' => true,
        ]);

        $normalInspection = Inspection::query()->create([
            'inspection_number' => 'INSP-RESULT-0001',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $officerOne->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_NORMAL,
            'submitted_at' => now(),
        ]);

        $abnormalInspection = Inspection::query()->create([
            'inspection_number' => 'INSP-RESULT-0002',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $officerTwo->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_ABNORMAL,
            'submitted_at' => now(),
        ]);

        $ticketStatus = TicketStatus::query()->create([
            'code' => 'NEW',
            'name' => 'New',
            'is_open' => true,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-RESULT-0001',
            'title' => 'Auto ticket abnormal',
            'description' => 'Generated from abnormal inspection',
            'ticket_status_id' => $ticketStatus->id,
            'inspection_id' => $abnormalInspection->id,
            'source' => 'inspection_auto',
            'impact' => 'high',
            'urgency' => 'high',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/results')
            ->assertOk()
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.normal', 1)
            ->assertJsonPath('summary.abnormal', 1)
            ->assertJsonPath('summary.with_ticket', 1)
            ->assertJsonFragment(['inspection_number' => $normalInspection->inspection_number])
            ->assertJsonFragment(['inspection_number' => $abnormalInspection->inspection_number])
            ->assertJsonFragment(['inspection_officer_name' => $officerOne->name])
            ->assertJsonFragment(['inspection_officer_name' => $officerTwo->name]);
    }

    public function test_inspection_officer_can_only_view_own_inspection_results(): void
    {
        $officer = User::factory()->create([
            'email' => 'officer.owned.results@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

        $otherOfficer = User::factory()->create([
            'email' => 'officer.other.results@example.com',
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-OWNED-API',
            'name' => 'Inspection Owned API',
            'is_active' => true,
        ]);

        $ownedInspection = Inspection::query()->create([
            'inspection_number' => 'INSP-OWNED-0001',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $officer->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_NORMAL,
            'submitted_at' => now(),
        ]);

        $otherInspection = Inspection::query()->create([
            'inspection_number' => 'INSP-OWNED-0002',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $otherOfficer->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_ABNORMAL,
            'submitted_at' => now(),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/results')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['inspection_number' => $ownedInspection->inspection_number])
            ->assertJsonMissing(['inspection_number' => $otherInspection->inspection_number]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/results/'.$otherInspection->id)
            ->assertForbidden();
    }
}

