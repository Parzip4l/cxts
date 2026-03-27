<?php

namespace Tests\Feature\Web;

use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionResultWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_view_inspection_results_page_with_officer_information(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
        ]);

        $officerOne = User::factory()->create([
            'name' => 'Officer One',
            'role' => 'inspection_officer',
        ]);

        $officerTwo = User::factory()->create([
            'name' => 'Officer Two',
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-WEB-RESULT',
            'name' => 'Web Result Template',
            'is_active' => true,
        ]);

        Inspection::query()->create([
            'inspection_number' => 'INSP-WEB-0001',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $officerOne->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_NORMAL,
            'submitted_at' => now(),
        ]);

        Inspection::query()->create([
            'inspection_number' => 'INSP-WEB-0002',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $officerTwo->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_ABNORMAL,
            'submitted_at' => now(),
        ]);

        $this->actingAs($supervisor)
            ->get(route('inspection-results.index'))
            ->assertOk()
            ->assertSee('INSP-WEB-0001')
            ->assertSee('INSP-WEB-0002')
            ->assertSee('Officer One')
            ->assertSee('Officer Two');
    }

    public function test_inspection_officer_cannot_access_other_officer_result_detail(): void
    {
        $officer = User::factory()->create([
            'role' => 'inspection_officer',
        ]);

        $otherOfficer = User::factory()->create([
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-WEB-ACCESS',
            'name' => 'Web Access Template',
            'is_active' => true,
        ]);

        $otherInspection = Inspection::query()->create([
            'inspection_number' => 'INSP-WEB-0003',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $otherOfficer->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_SUBMITTED,
            'final_result' => Inspection::FINAL_RESULT_ABNORMAL,
            'submitted_at' => now(),
        ]);

        $this->actingAs($officer)
            ->get(route('inspection-results.show', $otherInspection))
            ->assertForbidden();
    }
}

