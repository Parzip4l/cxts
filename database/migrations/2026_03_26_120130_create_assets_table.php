<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('asset_category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('department_owner_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->string('serial_number', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->date('install_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->string('criticality', 30)->default('medium');
            $table->foreignId('asset_status_id')->nullable()->constrained('asset_statuses')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['asset_category_id', 'asset_status_id', 'is_active']);
            $table->index(['service_id', 'asset_status_id', 'is_active']);
            $table->index(['department_owner_id', 'is_active']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['asset_location_id', 'is_active']);
            $table->index(['criticality', 'is_active']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
