<?php

namespace Database\Seeders;

use App\Models\TicketPriority;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['code' => 'P1', 'name' => 'Critical', 'level' => 1, 'response_target_minutes' => 15, 'resolution_target_minutes' => 120],
            ['code' => 'P2', 'name' => 'High', 'level' => 2, 'response_target_minutes' => 30, 'resolution_target_minutes' => 240],
            ['code' => 'P3', 'name' => 'Medium', 'level' => 3, 'response_target_minutes' => 60, 'resolution_target_minutes' => 480],
            ['code' => 'P4', 'name' => 'Low', 'level' => 4, 'response_target_minutes' => 240, 'resolution_target_minutes' => 1440],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::query()->updateOrCreate(
                ['code' => $priority['code']],
                [
                    'name' => $priority['name'],
                    'level' => $priority['level'],
                    'response_target_minutes' => $priority['response_target_minutes'],
                    'resolution_target_minutes' => $priority['resolution_target_minutes'],
                    'is_active' => true,
                ]
            );
        }
    }
}
