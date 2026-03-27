<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->string('title', 200);
            $table->text('description');
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('requester_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('ticket_category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('ticket_subcategory_id')->nullable()->constrained('ticket_subcategories')->nullOnDelete();
            $table->foreignId('ticket_priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('ticket_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete();
            $table->string('assigned_team_name', 100)->nullable();
            $table->foreignId('assigned_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sla_policy_name', 100)->nullable();
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->string('source', 30)->default('web');
            $table->string('impact', 30)->default('medium');
            $table->string('urgency', 30)->default('medium');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_status_changed_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['requester_id', 'created_at']);
            $table->index(['requester_department_id', 'created_at']);
            $table->index(['ticket_status_id', 'ticket_priority_id']);
            $table->index(['ticket_category_id', 'ticket_subcategory_id']);
            $table->index(['service_id', 'asset_id']);
            $table->index(['assigned_engineer_id', 'ticket_status_id']);
            $table->index(['response_due_at', 'resolution_due_at']);
            $table->index(['started_at', 'completed_at']);
            $table->index(['source', 'impact', 'urgency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
