<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('previous_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'assigned_at']);
            $table->index(['assigned_engineer_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assignments');
    }
};
