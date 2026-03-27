<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('service_category', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('ownership_model', 30)->default('internal');
            $table->foreignId('department_owner_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('service_manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['ownership_model', 'is_active']);
            $table->index(['department_owner_id', 'is_active']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['service_category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
