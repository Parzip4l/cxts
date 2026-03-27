<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('inspection_template_item_id')->nullable()->constrained('inspection_template_items')->nullOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('item_label', 200);
            $table->string('item_type', 30)->default('boolean');
            $table->string('expected_value', 120)->nullable();
            $table->string('result_value', 120)->nullable();
            $table->string('result_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('checked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['inspection_id', 'sequence']);
            $table->index(['inspection_id', 'result_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
