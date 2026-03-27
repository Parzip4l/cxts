<?php

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\InspectionTemplate;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class MyInspectionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspection_officer_can_create_fill_and_submit_inspection_via_api(): void
    {
        $officer = User::factory()->create([
            'email' => 'inspection-officer@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

        $assetCategory = AssetCategory::query()->create([
            'code' => 'CAT-ROUTER',
            'name' => 'Router',
            'is_active' => true,
        ]);

        $assetStatus = AssetStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_operational' => true,
            'is_active' => true,
        ]);

        $location = AssetLocation::query()->create([
            'code' => 'LOC-INS-001',
            'name' => 'Main Site',
            'is_active' => true,
        ]);

        $asset = Asset::query()->create([
            'code' => 'AST-INS-001',
            'name' => 'Router Main Site',
            'asset_category_id' => $assetCategory->id,
            'asset_status_id' => $assetStatus->id,
            'asset_location_id' => $location->id,
            'criticality' => Asset::CRITICALITY_HIGH,
            'is_active' => true,
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-ROUTER-DAILY',
            'name' => 'Daily Router Inspection',
            'asset_category_id' => $assetCategory->id,
            'is_active' => true,
        ]);

        $template->items()->createMany([
            [
                'sequence' => 1,
                'item_label' => 'Router is reachable',
                'item_type' => 'boolean',
                'expected_value' => 'PASS',
                'is_required' => true,
                'is_active' => true,
            ],
            [
                'sequence' => 2,
                'item_label' => 'CPU usage',
                'item_type' => 'number',
                'expected_value' => '<70',
                'is_required' => true,
                'is_active' => true,
            ],
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections', [
                'inspection_template_id' => $template->id,
                'asset_id' => $asset->id,
                'asset_location_id' => $location->id,
                'inspection_date' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.inspection_template_id', $template->id)
            ->assertJsonCount(2, 'data.items');

        $inspectionId = (int) $createResponse->json('data.id');
        $itemId = (int) $createResponse->json('data.items.0.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections/'.$inspectionId.'/items', [
                'items' => [
                    [
                        'id' => $itemId,
                        'result_status' => 'pass',
                        'result_value' => 'PASS',
                        'notes' => 'Reachable from monitoring host.',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.items.0.result_status', 'pass');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections/'.$inspectionId.'/submit', [
                'final_result' => 'normal',
                'summary_notes' => 'Inspection completed without critical findings.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.final_result', 'normal');

        $this->assertDatabaseHas('inspections', [
            'id' => $inspectionId,
            'status' => 'submitted',
            'final_result' => 'normal',
        ]);
    }

    public function test_submit_abnormal_requires_supporting_evidence(): void
    {
        $officer = User::factory()->create([
            'email' => 'inspection-officer-'.Str::lower(Str::random(6)).'@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-REQ-EVID',
            'name' => 'Template Evidence Requirement',
            'is_active' => true,
        ]);

        $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Check indicator',
            'item_type' => 'boolean',
            'is_required' => true,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections', [
                'inspection_template_id' => $template->id,
                'inspection_date' => now()->toDateString(),
            ])
            ->assertCreated();

        $inspectionId = (int) $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections/'.$inspectionId.'/submit', [
                'final_result' => 'abnormal',
                'summary_notes' => 'Abnormal found but no evidence.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['supporting_files']);
    }

    public function test_submit_abnormal_auto_creates_linked_ticket_once(): void
    {
        $officer = User::factory()->create([
            'email' => 'inspection-officer-auto-ticket@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

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
            'code' => 'INSP-AUTO-TICKET',
            'name' => 'Template Auto Ticket',
            'is_active' => true,
        ]);

        $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Check service availability',
            'item_type' => 'boolean',
            'is_required' => true,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections', [
                'inspection_template_id' => $template->id,
                'inspection_date' => now()->toDateString(),
            ])
            ->assertCreated();

        $inspectionId = (int) $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/inspection/my-inspections/'.$inspectionId.'/submit', [
                'final_result' => 'abnormal',
                'summary_notes' => 'Major abnormal finding requires ticket escalation.',
                'supporting_files' => [UploadedFile::fake()->image('abnormal-proof.jpg')],
            ])
            ->assertOk()
            ->assertJsonPath('data.final_result', 'abnormal');

        $this->assertDatabaseHas('tickets', [
            'inspection_id' => $inspectionId,
            'source' => 'inspection_auto',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections/'.$inspectionId.'/submit', [
                'final_result' => 'abnormal',
                'summary_notes' => 'Resubmit abnormal without creating duplicate.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertEquals(1, Ticket::query()->where('inspection_id', $inspectionId)->count());
    }

    public function test_submit_recurring_inspection_creates_next_scheduled_task(): void
    {
        $officer = User::factory()->create([
            'email' => 'inspection-officer-recurring@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-RECURRING-DAILY',
            'name' => 'Recurring Daily Inspection',
            'is_active' => true,
        ]);

        $template->items()->create([
            'sequence' => 1,
            'item_label' => 'Check signal status',
            'item_type' => 'boolean',
            'is_required' => true,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections', [
                'inspection_template_id' => $template->id,
                'inspection_date' => now()->toDateString(),
                'schedule_type' => 'daily',
                'schedule_interval' => 1,
            ])
            ->assertCreated();

        $inspectionId = (int) $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection/my-inspections/'.$inspectionId.'/submit', [
                'final_result' => 'normal',
                'summary_notes' => 'Daily recurring inspection completed.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('inspections', [
            'parent_inspection_id' => $inspectionId,
            'inspection_officer_id' => $officer->id,
            'status' => 'draft',
            'inspection_date' => now()->addDay()->startOfDay()->toDateTimeString(),
            'schedule_type' => 'daily',
            'schedule_interval' => 1,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/my-inspections')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
