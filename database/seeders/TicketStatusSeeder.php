<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'NEW', 'name' => 'New', 'is_open' => true, 'is_in_progress' => false, 'is_closed' => false],
            ['code' => 'PENDING_APPROVAL', 'name' => 'Pending Approval', 'is_open' => true, 'is_in_progress' => false, 'is_closed' => false],
            ['code' => 'REJECTED', 'name' => 'Rejected', 'is_open' => false, 'is_in_progress' => false, 'is_closed' => true],
            ['code' => 'ASSIGNED', 'name' => 'Assigned', 'is_open' => true, 'is_in_progress' => false, 'is_closed' => false],
            ['code' => 'IN_PROGRESS', 'name' => 'In Progress', 'is_open' => true, 'is_in_progress' => true, 'is_closed' => false],
            ['code' => 'ON_HOLD', 'name' => 'On Hold', 'is_open' => true, 'is_in_progress' => false, 'is_closed' => false],
            ['code' => 'COMPLETED', 'name' => 'Completed', 'is_open' => false, 'is_in_progress' => false, 'is_closed' => true],
            ['code' => 'CLOSED', 'name' => 'Closed', 'is_open' => false, 'is_in_progress' => false, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            TicketStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'is_open' => $status['is_open'],
                    'is_in_progress' => $status['is_in_progress'],
                    'is_closed' => $status['is_closed'],
                    'is_active' => true,
                ]
            );
        }
    }
}
