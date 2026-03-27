<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_skill_ticket_detail_subcategory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engineer_skill_id')->constrained('engineer_skills')->cascadeOnDelete();
            $table->foreignId('ticket_detail_subcategory_id')->constrained('ticket_detail_subcategories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['engineer_skill_id', 'ticket_detail_subcategory_id'], 'engineer_skill_detail_subcategory_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_skill_ticket_detail_subcategory');
    }
};
