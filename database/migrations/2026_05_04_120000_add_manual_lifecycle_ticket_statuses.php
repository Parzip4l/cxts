<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            ['code' => 'PENDING_CUSTOMER', 'name' => 'Pending Customer', 'is_open' => true, 'is_in_progress' => false, 'is_closed' => false, 'is_active' => true],
            ['code' => 'CANCELLED', 'name' => 'Cancelled', 'is_open' => false, 'is_in_progress' => false, 'is_closed' => true, 'is_active' => true],
        ];

        foreach ($statuses as $status) {
            DB::table('ticket_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }
    }

    public function down(): void
    {
        DB::table('ticket_statuses')
            ->whereIn('code', ['PENDING_CUSTOMER', 'CANCELLED'])
            ->delete();
    }
};
