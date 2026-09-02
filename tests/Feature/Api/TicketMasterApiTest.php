<?php

namespace Tests\Feature\Api;

use App\Models\ServiceCatalog;
use App\Models\SlaPolicy;
use App\Models\SlaPolicyAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketMasterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_ticket_master_data_via_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'ticket-master-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $categoryResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-categories', [
                'code' => 'INCIDENT',
                'name' => 'Incident',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'INCIDENT');

        $categoryId = (int) $categoryResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-subcategories', [
                'ticket_category_id' => $categoryId,
                'code' => 'NETWORK_DOWN',
                'name' => 'Network Down',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.ticket_category_id', $categoryId);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-priorities', [
                'code' => 'P2',
                'name' => 'High',
                'level' => 2,
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 240,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'P2');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-statuses', [
                'code' => 'ASSIGNED',
                'name' => 'Assigned',
                'is_open' => true,
                'is_in_progress' => false,
                'is_closed' => false,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'ASSIGNED');
    }

    public function test_super_admin_can_manage_service_request_defaults_via_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'service-catalog-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $slaPolicy = SlaPolicy::query()->create([
            'name' => 'Service Request Standard',
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 480,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/services', [
                'code' => 'SR-CATALOG-001',
                'name' => 'Employee Access Request',
                'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
                'is_active' => true,
                'is_requestable' => true,
                'default_request_approval_required' => true,
                'default_request_sla_policy_id' => $slaPolicy->id,
                'fulfillment_team_name' => 'Access Fulfillment',
                'request_form_schema' => '[{"name":"employee_id","label":"Employee ID","type":"text"}]',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'SR-CATALOG-001')
            ->assertJsonPath('data.is_requestable', true)
            ->assertJsonPath('data.default_request_approval_required', true)
            ->assertJsonPath('data.default_request_sla_policy_id', $slaPolicy->id)
            ->assertJsonPath('data.fulfillment_team_name', 'Access Fulfillment');

        $this->assertDatabaseHas('services', [
            'id' => (int) $response->json('data.id'),
            'is_requestable' => true,
            'default_request_approval_required' => true,
            'default_request_sla_policy_id' => $slaPolicy->id,
            'fulfillment_team_name' => 'Access Fulfillment',
        ]);
    }

    public function test_super_admin_sla_policy_changes_are_snapshotted(): void
    {
        $admin = User::factory()->create([
            'email' => 'sla-policy-audit-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sla-policies', [
                'name' => 'Audited SLA',
                'response_time_minutes' => 30,
                'resolution_time_minutes' => 240,
                'escalate_on_warning' => true,
                'escalate_on_breach' => true,
                'escalation_role_code' => 'supervisor',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.escalate_on_warning', true)
            ->assertJsonPath('data.escalate_on_breach', true)
            ->assertJsonPath('data.escalation_role_code', 'supervisor');

        $slaPolicyId = (int) $createResponse->json('data.id');

        $this->assertDatabaseHas('sla_policy_audit_logs', [
            'sla_policy_id' => $slaPolicyId,
            'action' => SlaPolicyAuditLog::ACTION_CREATED,
            'actor_user_id' => $admin->id,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/sla-policies/'.$slaPolicyId, [
                'name' => 'Audited SLA',
                'response_time_minutes' => 45,
                'resolution_time_minutes' => 240,
                'escalate_on_warning' => false,
                'escalate_on_breach' => true,
                'escalation_role_code' => 'operational_admin',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.response_time_minutes', 45)
            ->assertJsonPath('data.escalate_on_warning', false)
            ->assertJsonPath('data.escalation_role_code', 'operational_admin');

        $updateLog = SlaPolicyAuditLog::query()
            ->where('sla_policy_id', $slaPolicyId)
            ->where('action', SlaPolicyAuditLog::ACTION_UPDATED)
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertSame(30, $updateLog->before_snapshot['response_time_minutes']);
        $this->assertSame(45, $updateLog->after_snapshot['response_time_minutes']);
        $this->assertSame('supervisor', $updateLog->before_snapshot['escalation_role_code']);
        $this->assertSame('operational_admin', $updateLog->after_snapshot['escalation_role_code']);
    }
}
