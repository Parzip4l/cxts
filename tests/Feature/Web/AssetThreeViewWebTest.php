<?php

namespace Tests\Feature\Web;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetThreeViewWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_index_uses_three_location_views_and_filters_by_selected_location(): void
    {
        $admin = User::factory()->create([
            'role' => 'operational_admin',
        ]);

        $category = AssetCategory::query()->create([
            'code' => 'CAT-THREE',
            'name' => 'Network Device',
            'is_active' => true,
        ]);

        $locationA = AssetLocation::query()->create([
            'code' => 'LOC-A',
            'name' => 'Site A',
            'is_active' => true,
        ]);

        $locationB = AssetLocation::query()->create([
            'code' => 'LOC-B',
            'name' => 'Site B',
            'is_active' => true,
        ]);

        $locationC = AssetLocation::query()->create([
            'code' => 'LOC-C',
            'name' => 'Site C',
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-A-001',
            'name' => 'Router Site A',
            'asset_category_id' => $category->id,
            'asset_location_id' => $locationA->id,
            'criticality' => Asset::CRITICALITY_HIGH,
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-B-001',
            'name' => 'Router Site B',
            'asset_category_id' => $category->id,
            'asset_location_id' => $locationB->id,
            'criticality' => Asset::CRITICALITY_MEDIUM,
            'is_active' => true,
        ]);

        Asset::query()->create([
            'code' => 'AST-C-001',
            'name' => 'Router Site C',
            'asset_category_id' => $category->id,
            'asset_location_id' => $locationC->id,
            'criticality' => Asset::CRITICALITY_LOW,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('master-data.assets.index'))
            ->assertOk()
            ->assertSee('Three View by Location')
            ->assertSee('Site A')
            ->assertSee('Site B')
            ->assertSee('Site C')
            ->assertSee('Router Site A')
            ->assertDontSee('Router Site B');

        $this->actingAs($admin)
            ->get(route('master-data.assets.index', ['location_view' => $locationB->id]))
            ->assertOk()
            ->assertSee('Router Site B')
            ->assertDontSee('Router Site A');
    }
}

