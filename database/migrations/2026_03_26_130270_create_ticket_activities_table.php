<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type', 50);
            $table->foreignId('old_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete();
            $table->foreignId('new_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
