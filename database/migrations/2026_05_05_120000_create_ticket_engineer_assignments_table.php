<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_engineer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('team_name', 100)->nullable();
            $table->decimal('score_share', 8, 4)->default(1);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'engineer_id']);
            $table->index(['engineer_id', 'assigned_at']);
            $table->index(['ticket_id', 'score_share']);
        });

        DB::table('tickets')
            ->whereNotNull('assigned_engineer_id')
            ->orderBy('id')
            ->get(['id', 'assigned_engineer_id', 'updated_by_id', 'assigned_team_name', 'created_at', 'updated_at'])
            ->each(function ($ticket): void {
                DB::table('ticket_engineer_assignments')->insert([
                    'ticket_id' => $ticket->id,
                    'engineer_id' => $ticket->assigned_engineer_id,
                    'assigned_by_id' => $ticket->updated_by_id,
                    'team_name' => $ticket->assigned_team_name,
                    'score_share' => 1,
                    'assigned_at' => $ticket->updated_at ?? $ticket->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_engineer_assignments');
    }
};
