<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_template_id')->constrained('inspection_templates')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('item_label', 200);
            $table->string('item_type', 30)->default('boolean');
            $table->string('expected_value', 120)->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['inspection_template_id', 'sequence']);
            $table->index(['inspection_template_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_template_items');
    }
};
