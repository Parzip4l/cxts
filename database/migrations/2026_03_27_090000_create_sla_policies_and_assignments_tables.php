<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('response_time_minutes')->nullable();
            $table->unsignedInteger('resolution_time_minutes')->nullable();
            $table->unsignedBigInteger('working_hours_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'name']);
            $table->index('working_hours_id');
        });

        Schema::create('sla_policy_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->string('ticket_type', 50)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('ticket_subcategories')->nullOnDelete();
            $table->foreignId('service_item_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->string('impact', 30)->nullable();
            $table->string('urgency', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['ticket_type', 'category_id', 'subcategory_id'], 'sla_assignments_type_category_sub_idx');
            $table->index(['service_item_id', 'priority_id'], 'sla_assignments_service_priority_idx');
            $table->index(['impact', 'urgency'], 'sla_assignments_impact_urgency_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policy_assignments');
        Schema::dropIfExists('sla_policies');
    }
};
