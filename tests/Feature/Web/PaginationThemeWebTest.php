<?php

namespace Tests\Feature\Web;

use App\Models\EngineerSchedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationThemeWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_pages_use_bootstrap_pagination_template(): void
    {
        $admin = User::factory()->create([
            'role' => 'operational_admin',
        ]);

        $engineer = User::factory()->create([
            'role' => 'engineer',
        ]);

        $shift = Shift::query()->create([
            'code' => 'SHIFT-PAG-01',
            'name' => 'Pagination Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'break_minutes' => 60,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        for ($i = 0; $i < 20; $i++) {
            EngineerSchedule::query()->create([
                'user_id' => $engineer->id,
                'shift_id' => $shift->id,
                'work_date' => now()->addDays($i)->toDateString(),
                'status' => EngineerSchedule::STATUS_ASSIGNED,
                'assigned_by_id' => $admin->id,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('master-data.engineer-schedules.index'))
            ->assertOk()
            ->assertSee('pagination')
            ->assertSee('page-item')
            ->assertDontSee('w-5 h-5');
    }
}

