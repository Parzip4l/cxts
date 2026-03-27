<?php

namespace Tests\Feature\Api;

use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Department;
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

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/assets', [
                'code' => 'AST-RT-001',
                'name' => 'Router Core A',
                'asset_category_id' => $category->id,
                'asset_status_id' => $status->id,
                'criticality' => 'high',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'AST-RT-001');

        $this->assertDatabaseHas('assets', [
            'code' => 'AST-RT-001',
            'name' => 'Router Core A',
        ]);
    }
}
