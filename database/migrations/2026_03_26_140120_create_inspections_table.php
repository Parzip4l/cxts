<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number', 50)->unique();
            $table->foreignId('inspection_template_id')->nullable()->constrained('inspection_templates')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('inspection_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('inspection_date');
            $table->string('status', 30)->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('summary_notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['inspection_officer_id', 'inspection_date']);
            $table->index(['status', 'inspection_date']);
            $table->index(['asset_id', 'inspection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
