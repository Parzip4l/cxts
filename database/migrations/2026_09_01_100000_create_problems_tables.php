<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table): void {
            $table->id();
            $table->string('problem_number', 50)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('open');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ticket_priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->text('symptom')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('permanent_fix')->nullable();
            $table->boolean('is_known_error')->default(false);
            $table->text('action_item')->nullable();
            $table->timestamp('target_resolution_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'is_known_error']);
            $table->index(['owner_user_id', 'status']);
            $table->index(['ticket_priority_id', 'status']);
        });

        Schema::create('problem_ticket', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('problem_id')->constrained('problems')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['problem_id', 'ticket_id']);
            $table->index(['ticket_id', 'problem_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_ticket');
        Schema::dropIfExists('problems');
    }
};
