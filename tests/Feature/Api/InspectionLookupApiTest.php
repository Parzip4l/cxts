<?php

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\InspectionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionLookupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspection_officer_can_fetch_templates_assets_and_locations(): void
    {
        $officer = User::factory()->create([
            'email' => 'officer.lookup@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'inspection_officer',
        ]);

        $assetCategory = AssetCategory::query()->create([
            'code' => 'CAT-LOOKUP',
            'name' => 'Lookup Category',
            'is_active' => true,
        ]);

        $assetStatus = AssetStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_operational' => true,
            'is_active' => true,
        ]);

        $location = AssetLocation::query()->create([
            'code' => 'LOC-LOOKUP-001',
            'name' => 'Lookup Location',
            'is_active' => true,
        ]);

        $asset = Asset::query()->create([
            'code' => 'AST-LOOKUP-001',
            'name' => 'Lookup Asset',
            'asset_category_id' => $assetCategory->id,
            'asset_status_id' => $assetStatus->id,
            'asset_location_id' => $location->id,
            'criticality' => Asset::CRITICALITY_MEDIUM,
            'is_active' => true,
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-LOOKUP-001',
            'name' => 'Lookup Template',
            'asset_category_id' => $assetCategory->id,
            'is_active' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $officer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/templates')
            ->assertOk()
            ->assertJsonFragment(['id' => $template->id]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/assets')
            ->assertOk()
            ->assertJsonFragment(['id' => $asset->id]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/asset-locations')
            ->assertOk()
            ->assertJsonFragment(['id' => $location->id]);
    }

    public function test_non_inspection_actor_cannot_access_inspection_lookup_endpoints(): void
    {
        $requester = User::factory()->create([
            'email' => 'requester.lookup@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'requester',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $requester->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/templates')
            ->assertForbidden();
    }

    public function test_engineer_can_access_inspection_lookup_endpoints(): void
    {
        $engineer = User::factory()->create([
            'email' => 'engineer.lookup.allowed@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/inspection/templates')
            ->assertOk();
    }
}
