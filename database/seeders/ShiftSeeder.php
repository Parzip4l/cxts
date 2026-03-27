<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'code' => 'SHIFT-DAY',
                'name' => 'Day Shift',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'break_minutes' => 60,
                'is_overnight' => false,
                'description' => 'Regular day shift.',
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-EVE',
                'name' => 'Evening Shift',
                'start_time' => '16:00',
                'end_time' => '00:00',
                'break_minutes' => 60,
                'is_overnight' => false,
                'description' => 'Evening operational shift.',
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-NIGHT',
                'name' => 'Night Shift',
                'start_time' => '00:00',
                'end_time' => '08:00',
                'break_minutes' => 60,
                'is_overnight' => true,
                'description' => 'Night monitoring shift.',
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::query()->updateOrCreate(
                ['code' => $shift['code']],
                $shift,
            );
        }
    }
}
