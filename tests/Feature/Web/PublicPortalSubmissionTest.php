<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\InspectionTemplate;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PublicPortalSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_public_ticket(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-PUBLIC',
            'name' => 'Public Department',
            'is_active' => true,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        TicketPriority::query()->create([
            'code' => 'P3',
            'name' => 'Medium',
            'level' => 3,
            'response_target_minutes' => 60,
            'resolution_target_minutes' => 480,
            'is_active' => true,
        ]);

        $priorityId = (int) TicketPriority::query()->value('id');

        TicketStatus::query()->create([
            'code' => 'NEW',
            'name' => 'New',
            'is_open' => true,
            'is_active' => true,
        ]);

        $this->post(route('public.tickets.store'), [
            'requester_name' => 'Public User',
            'requester_email' => 'public.user@example.com',
            'requester_department_id' => $department->id,
            'title' => 'Internet down in meeting room',
            'description' => 'Cannot connect to WiFi from 10 AM.',
            'ticket_category_id' => $category->id,
            'ticket_priority_id' => $priorityId,
            'impact' => 'medium',
            'urgency' => 'medium',
        ])->assertRedirect(route('public.tickets.create'));

        $this->assertDatabaseHas('tickets', [
            'title' => 'Internet down in meeting room',
            'source' => 'public_web',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'public.user@example.com',
        ]);
    }

    public function test_guest_can_submit_public_inspection(): void
    {
        $template = InspectionTemplate::query()->create([
            'code' => 'PUB-INSP-01',
            'name' => 'Public Inspection Template',
            'is_active' => true,
        ]);

        $item = $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Device reachable',
            'item_type' => 'boolean',
            'expected_value' => 'PASS',
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->post(route('public.inspections.store'), [
            'reporter_name' => 'Field Officer',
            'reporter_email' => 'field.officer@example.com',
            'inspection_template_id' => $template->id,
            'inspection_date' => now()->toDateString(),
            'final_result' => 'normal',
            'summary_notes' => 'Checked from public page',
            'items' => [
                [
                    'inspection_template_item_id' => $item->id,
                    'result_status' => 'pass',
                    'result_value' => 'PASS',
                    'notes' => 'All good',
                ],
            ],
        ])->assertRedirect(route('public.inspections.create'));

        $this->assertDatabaseHas('inspections', [
            'inspection_template_id' => $template->id,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('inspection_items', [
            'inspection_template_item_id' => $item->id,
            'result_status' => 'pass',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'public-inspection@system.local',
            'role' => 'inspection_officer',
        ]);
    }

    public function test_public_inspection_abnormal_requires_supporting_file(): void
    {
        $template = InspectionTemplate::query()->create([
            'code' => 'PUB-INSP-02',
            'name' => 'Public Inspection Template 2',
            'is_active' => true,
        ]);

        $item = $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Signal quality check',
            'item_type' => 'boolean',
            'expected_value' => 'PASS',
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->from(route('public.inspections.create'))
            ->post(route('public.inspections.store'), [
                'reporter_name' => 'Field Officer',
                'reporter_email' => 'field.officer2@example.com',
                'inspection_template_id' => $template->id,
                'inspection_date' => now()->toDateString(),
                'final_result' => 'abnormal',
                'items' => [
                    [
                        'inspection_template_item_id' => $item->id,
                        'result_status' => 'fail',
                        'result_value' => 'FAIL',
                    ],
                ],
            ])
            ->assertRedirect(route('public.inspections.create'))
            ->assertSessionHasErrors(['supporting_files']);
    }

    public function test_public_inspection_abnormal_auto_creates_ticket(): void
    {
        TicketStatus::query()->create([
            'code' => 'NEW',
            'name' => 'New',
            'is_open' => true,
            'is_active' => true,
        ]);

        TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'response_target_minutes' => 30,
            'resolution_target_minutes' => 240,
            'is_active' => true,
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'PUB-INSP-03',
            'name' => 'Public Inspection Template 3',
            'is_active' => true,
        ]);

        $item = $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Power status check',
            'item_type' => 'boolean',
            'expected_value' => 'PASS',
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->post(route('public.inspections.store'), [
            'reporter_name' => 'Public Field Team',
            'reporter_email' => 'public.field@example.com',
            'inspection_template_id' => $template->id,
            'inspection_date' => now()->toDateString(),
            'final_result' => 'abnormal',
            'summary_notes' => 'Device down and needs urgent follow-up.',
            'supporting_files' => [UploadedFile::fake()->image('public-abnormal.jpg')],
            'items' => [
                [
                    'inspection_template_item_id' => $item->id,
                    'result_status' => 'fail',
                    'result_value' => 'FAIL',
                    'notes' => 'No power indicator.',
                ],
            ],
        ])->assertRedirect(route('public.inspections.create'));

        $inspectionId = (int) \App\Models\Inspection::query()->where('inspection_template_id', $template->id)->value('id');

        $this->assertDatabaseHas('tickets', [
            'inspection_id' => $inspectionId,
            'source' => 'inspection_auto',
        ]);

        $this->assertEquals(1, Ticket::query()->where('inspection_id', $inspectionId)->count());
    }
}
