<?php

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\SlaPolicy;
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

        $criticalPriority = TicketPriority::query()->create([
            'code' => 'P1',
            'name' => 'Critical',
            'level' => 1,
            'response_target_minutes' => 15,
            'resolution_target_minutes' => 120,
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
        TicketStatus::query()->create(['code' => 'PENDING_APPROVAL', 'name' => 'Pending Approval', 'is_open' => true, 'is_active' => true]);
        TicketStatus::query()->create(['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_active' => true]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets', [
                'title' => 'Koneksi internet kantor putus',
                'description' => 'Semua user tidak bisa akses internet sejak 08:15.',
                'process_type' => 'incident',
                'incident_detection_source' => 'user_report',
                'is_major_incident' => true,
                'affected_users_count' => 45,
                'service_impact_note' => 'Internet kantor utama tidak tersedia untuk sebagian besar user.',
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
            ->assertJsonPath('data.process_type', 'incident')
            ->assertJsonPath('data.process_type_label', 'Incident')
            ->assertJsonPath('data.incident_detection_source', 'user_report')
            ->assertJsonPath('data.incident_detection_source_label', 'User Report')
            ->assertJsonPath('data.is_major_incident', true)
            ->assertJsonPath('data.affected_users_count', 45)
            ->assertJsonPath('data.requester_id', $requester->id);

        $ticketId = (int) $createResponse->json('data.id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'process_type' => 'incident',
            'incident_detection_source' => 'user_report',
            'is_major_incident' => true,
            'affected_users_count' => 45,
            'ticket_priority_id' => $priority->id,
        ]);

        $autoPriorityResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets', [
                'title' => 'Core switch unreachable',
                'description' => 'Monitoring dan user report menunjukkan core switch tidak dapat diakses.',
                'process_type' => 'incident',
                'requester_id' => $requester->id,
                'requester_department_id' => $department->id,
                'ticket_category_id' => $category->id,
                'ticket_subcategory_id' => $subcategory->id,
                'source' => 'api',
                'impact' => 'high',
                'urgency' => 'high',
            ])
            ->assertCreated()
            ->assertJsonPath('data.ticket_priority_id', $criticalPriority->id)
            ->assertJsonPath('data.ticket_priority_name', 'Critical');

        $this->assertDatabaseHas('tickets', [
            'id' => (int) $autoPriorityResponse->json('data.id'),
            'ticket_priority_id' => $criticalPriority->id,
            'impact' => 'high',
            'urgency' => 'high',
        ]);

        $requestCategory = TicketCategory::query()->create([
            'code' => 'REQUEST',
            'name' => 'Service Request',
            'requires_approval' => false,
            'allow_direct_assignment' => true,
            'is_active' => true,
        ]);

        $serviceManager = User::factory()->create([
            'email' => 'service.manager.ticket@example.com',
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $requestSla = SlaPolicy::query()->create([
            'name' => 'Access Request SLA',
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 480,
            'is_active' => true,
        ]);

        $requestService = ServiceCatalog::query()->create([
            'code' => 'SRV-ACCESS-001',
            'name' => 'Employee Access Service',
            'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
            'department_owner_id' => $department->id,
            'service_manager_user_id' => $serviceManager->id,
            'is_active' => true,
            'is_requestable' => true,
            'default_request_approval_required' => true,
            'default_request_sla_policy_id' => $requestSla->id,
            'fulfillment_team_name' => 'Access Fulfillment',
        ]);

        $serviceRequestResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets', [
                'title' => 'Request akun aplikasi finance',
                'description' => 'User baru membutuhkan akses aplikasi finance.',
                'process_type' => 'service_request',
                'requester_id' => $requester->id,
                'requester_department_id' => $department->id,
                'ticket_category_id' => $requestCategory->id,
                'service_id' => $requestService->id,
                'source' => 'api',
                'impact' => 'medium',
                'urgency' => 'medium',
                'request_form_payload' => [
                    'employee_id' => 'EMP-001',
                    'access_level' => 'read_only',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.process_type', 'service_request')
            ->assertJsonPath('data.requires_approval', true)
            ->assertJsonPath('data.expected_approver_id', $serviceManager->id)
            ->assertJsonPath('data.expected_approver_strategy', 'service_manager')
            ->assertJsonPath('data.flow_policy_source', 'service_catalog')
            ->assertJsonPath('data.sla_policy_id', $requestSla->id)
            ->assertJsonPath('data.assigned_team_name', 'Access Fulfillment')
            ->assertJsonPath('data.request_form_payload.employee_id', 'EMP-001');

        $this->assertDatabaseHas('tickets', [
            'id' => (int) $serviceRequestResponse->json('data.id'),
            'process_type' => 'service_request',
            'requires_approval' => true,
            'expected_approver_id' => $serviceManager->id,
            'sla_policy_id' => $requestSla->id,
            'assigned_team_name' => 'Access Fulfillment',
        ]);

        $changeCategory = TicketCategory::query()->create([
            'code' => 'MAINTENANCE',
            'name' => 'Maintenance Change',
            'requires_approval' => true,
            'allow_direct_assignment' => true,
            'approver_user_id' => $serviceManager->id,
            'approver_strategy' => 'specific_user',
            'is_active' => true,
        ]);

        $changeResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/tickets', [
                'title' => 'Upgrade firewall firmware',
                'description' => 'Upgrade minor firmware untuk firewall perimeter.',
                'process_type' => 'change_request',
                'requester_id' => $requester->id,
                'requester_department_id' => $department->id,
                'ticket_category_id' => $changeCategory->id,
                'service_id' => $service->id,
                'asset_id' => $asset->id,
                'source' => 'api',
                'impact' => 'medium',
                'urgency' => 'low',
                'change_reason' => 'Patch keamanan vendor.',
                'change_risk_level' => 'medium',
                'change_planned_start_at' => '2026-09-02 22:00:00',
                'change_planned_end_at' => '2026-09-02 23:00:00',
                'change_rollback_plan' => 'Rollback ke firmware sebelumnya dari backup config.',
                'change_affected_scope' => 'Internet perimeter dan VPN user.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.process_type', 'change_request')
            ->assertJsonPath('data.requires_approval', true)
            ->assertJsonPath('data.change_risk_level', 'medium')
            ->assertJsonPath('data.change_reason', 'Patch keamanan vendor.')
            ->assertJsonPath('data.change_rollback_plan', 'Rollback ke firmware sebelumnya dari backup config.');

        $this->assertDatabaseHas('tickets', [
            'id' => (int) $changeResponse->json('data.id'),
            'process_type' => 'change_request',
            'change_risk_level' => 'medium',
            'change_reason' => 'Patch keamanan vendor.',
            'change_affected_scope' => 'Internet perimeter dan VPN user.',
        ]);

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
