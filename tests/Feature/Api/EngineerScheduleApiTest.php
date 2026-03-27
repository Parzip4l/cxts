<?php

namespace Tests\Feature\Api;

use App\Models\EngineerSchedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineerScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_shift_and_engineer_schedule_via_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'schedule-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $engineer = User::factory()->create([
            'email' => 'schedule-engineer@example.com',
            'role' => 'engineer',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $shiftResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/shifts', [
                'code' => 'SHIFT-DAY',
                'name' => 'Day Shift',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'break_minutes' => 60,
                'is_overnight' => false,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'SHIFT-DAY');

        $shiftId = (int) $shiftResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/engineer-schedules', [
                'user_id' => $engineer->id,
                'shift_id' => $shiftId,
                'work_date' => now()->toDateString(),
                'status' => 'assigned',
                'notes' => 'Assigned from API test',
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $engineer->id)
            ->assertJsonPath('data.shift_id', $shiftId);

        $this->assertDatabaseHas('engineer_schedules', [
            'user_id' => $engineer->id,
            'shift_id' => $shiftId,
        ]);
    }

    public function test_engineer_can_view_only_own_schedules_via_api(): void
    {
        $engineer = User::factory()->create([
            'email' => 'my-schedule-engineer@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
        ]);

        $anotherEngineer = User::factory()->create([
            'email' => 'other-schedule-engineer@example.com',
            'role' => 'engineer',
        ]);

        $shift = Shift::query()->create([
            'code' => 'SHIFT-MY',
            'name' => 'My Shift',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        EngineerSchedule::query()->create([
            'user_id' => $engineer->id,
            'shift_id' => $shift->id,
            'work_date' => now()->toDateString(),
            'status' => EngineerSchedule::STATUS_ASSIGNED,
        ]);

        EngineerSchedule::query()->create([
            'user_id' => $anotherEngineer->id,
            'shift_id' => $shift->id,
            'work_date' => now()->addDay()->toDateString(),
            'status' => EngineerSchedule::STATUS_ASSIGNED,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/engineer/schedules')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $engineer->id);
    }
}
