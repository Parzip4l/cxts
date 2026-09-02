<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_commitments', function (Blueprint $table): void {
            $table->id();
            $table->string('commitment_number', 50)->unique();
            $table->string('name', 150);
            $table->string('commitment_type', 40);
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('provider_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->unsignedInteger('response_target_minutes')->nullable();
            $table->unsignedInteger('resolution_target_minutes')->nullable();
            $table->decimal('availability_target_percent', 5, 2)->nullable();
            $table->string('escalation_contact', 150)->nullable();
            $table->string('review_frequency', 60)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['commitment_type', 'status']);
            $table->index(['service_id', 'commitment_type']);
            $table->index(['provider_department_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_commitments');
    }
};
