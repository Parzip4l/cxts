<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ticket_category_id', 'code']);
            $table->index(['ticket_category_id', 'name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_subcategories');
    }
};
