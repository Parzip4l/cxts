<?php

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_asset_via_api(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-ENG',
            'name' => 'Engineering',
            'is_active' => true,
        ]);

        $category = AssetCategory::query()->create([
            'code' => 'CAT-ROUTER',
            'name' => 'Router',
            'is_active' => true,
        ]);

        $status = AssetStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_operational' => true,
            'is_active' => true,
        ]);

        $service = ServiceCatalog::query()->create([
            'code' => 'SRV-ASSET-CI',
            'name' => 'Asset CI Service',
            'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
            'department_owner_id' => $department->id,
            'is_active' => true,
        ]);

        User::factory()->create([
            'email' => 'asset-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
            'department_id' => $department->id,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'asset-admin@example.com',
            'password' => 'secret123',
        ])->json('token');

        $dependency = Asset::query()->create([
            'code' => 'AST-SW-001',
            'name' => 'Distribution Switch',
            'asset_category_id' => $category->id,
            'asset_status_id' => $status->id,
            'criticality' => 'critical',
            'is_active' => true,
        ]);

        $supported = Asset::query()->create([
            'code' => 'AST-AP-001',
            'name' => 'Office Access Point',
            'asset_category_id' => $category->id,
            'asset_status_id' => $status->id,
            'criticality' => 'medium',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/assets', [
                'code' => 'AST-RT-001',
                'name' => 'Router Core A',
                'asset_category_id' => $category->id,
                'asset_status_id' => $status->id,
                'criticality' => 'high',
                'service_id' => $service->id,
                'is_configuration_item' => true,
                'ci_type' => Asset::CI_TYPE_NETWORK,
                'ci_lifecycle_state' => Asset::CI_STATE_ACTIVE,
                'ci_governance_note' => 'Managed as service-impacting router CI.',
                'depends_on_asset_ids' => [$dependency->id],
                'supports_asset_ids' => [$supported->id],
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'AST-RT-001')
            ->assertJsonPath('data.is_configuration_item', true)
            ->assertJsonPath('data.ci_type', Asset::CI_TYPE_NETWORK)
            ->assertJsonPath('data.relationships.0.related_asset_id', $dependency->id)
            ->assertJsonPath('data.relationships.1.related_asset_id', $supported->id);

        $this->assertDatabaseHas('assets', [
            'code' => 'AST-RT-001',
            'name' => 'Router Core A',
        ]);

        $assetId = (int) $response->json('data.id');

        $this->assertDatabaseHas('asset_relationships', [
            'asset_id' => $assetId,
            'related_asset_id' => $dependency->id,
            'relationship_type' => 'depends_on',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/assets/'.$supported->id)
            ->assertOk()
            ->assertJsonPath('data.impact_view.0.asset_id', $assetId);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/assets', [
                'code' => 'AST-INVALID-CI',
                'name' => 'Invalid CI',
                'asset_category_id' => $category->id,
                'asset_status_id' => $status->id,
                'criticality' => 'medium',
                'is_configuration_item' => true,
                'is_active' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ci_type', 'service_id']);
    }
}
