<?php

namespace Tests\Feature\Api;

use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionTemplateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_inspection_template_via_api(): void
    {
        $assetCategory = AssetCategory::query()->create([
            'code' => 'CAT-AP',
            'name' => 'Access Point',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'email' => 'inspection-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inspection-templates', [
                'code' => 'INSP-DAILY-AP',
                'name' => 'Daily AP Inspection',
                'asset_category_id' => $assetCategory->id,
                'is_active' => true,
                'items' => [
                    [
                        'sequence' => 1,
                        'item_label' => 'Power indicator check',
                        'item_type' => 'boolean',
                        'expected_value' => 'ON',
                        'is_required' => true,
                        'is_active' => true,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'INSP-DAILY-AP')
            ->assertJsonCount(1, 'data.items');

        $this->assertDatabaseHas('inspection_templates', [
            'code' => 'INSP-DAILY-AP',
        ]);

        $this->assertDatabaseHas('inspection_template_items', [
            'item_label' => 'Power indicator check',
        ]);
    }
}
