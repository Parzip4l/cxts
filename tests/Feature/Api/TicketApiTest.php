<?php

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_create_and_assign_ticket_via_api(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-OPS',
            'name' => 'Operations',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'email' => 'supervisor.ticket@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'email' => 'requester.ticket@example.com',
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $engineer = User::factory()->create([
            'email' => 'engineer.ticket@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);

        $service = ServiceCatalog::query()->create([
            'code' => 'SRV-TICKET-001',
            'name' => 'Ops Connectivity Service',
            'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
            'department_owner_id' => $department->id,
            'is_active' => true,
        ]);

        $assetCategory = AssetCategory::query()->create([
            'code' => 'CAT-TICKET',
            'name' => 'Ticket Device',
            'is_active' => true,
        ]);

        $assetStatus = AssetStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_operational' => true,
            'is_active' => true,
        ]);

        $location = AssetLocation::query()->create([
            'code' => 'LOC-TICKET-001',
            'name' => 'Main Office',
            'is_active' => true,
        ]);

        $asset = Asset::query()->create([
            'code' => 'AST-TICKET-001',
            'name' => 'Core Router',
            'asset_category_id' => $assetCategory->id,
            'asset_status_id' => $assetStatus->id,
            'asset_location_id' => $location->id,
            'criticality' => Asset::CRITICALITY_HIGH,
            'is_active' => true,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        $subcategory = TicketSubcategory::query()->create([
            'ticket_category_id' => $category->id,
            'code' => 'NETWORK_DOWN',
            'name' => 'Network Down',
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
        TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets', [
                'title' => 'Koneksi internet kantor putus',
                'description' => 'Semua user tidak bisa akses internet sejak 08:15.',
                'requester_id' => $requester->id,
                'requester_department_id' => $department->id,
                'ticket_category_id' => $category->id,
                'ticket_subcategory_id' => $subcategory->id,
                'ticket_priority_id' => $priority->id,
                'service_id' => $service->id,
                'asset_id' => $asset->id,
                'asset_location_id' => $location->id,
                'source' => 'web',
                'impact' => 'high',
                'urgency' => 'high',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Koneksi internet kantor putus')
            ->assertJsonPath('data.requester_id', $requester->id);

        $ticketId = (int) $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets/'.$ticketId.'/assign', [
                'assigned_engineer_id' => $engineer->id,
                'assigned_team_name' => 'Field Team',
                'notes' => 'Segera cek router dan uplink utama.',
            ])
            ->assertOk()
            ->assertJsonPath('data.assigned_engineer_id', $engineer->id)
            ->assertJsonPath('data.assigned_team_name', 'Field Team');

        $this->assertDatabaseHas('ticket_assignments', [
            'ticket_id' => $ticketId,
            'assigned_engineer_id' => $engineer->id,
        ]);
    }
}
