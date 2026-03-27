<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('engineer_skill_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engineer_skill_id')->constrained('engineer_skills')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['engineer_skill_id', 'user_id']);
        });

        Schema::create('engineer_skill_service', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engineer_skill_id')->constrained('engineer_skills')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['engineer_skill_id', 'service_id']);
        });

        Schema::create('engineer_skill_ticket_subcategory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engineer_skill_id')->constrained('engineer_skills')->cascadeOnDelete();
            $table->foreignId('ticket_subcategory_id')->constrained('ticket_subcategories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['engineer_skill_id', 'ticket_subcategory_id']);
        });

        Schema::create('asset_category_engineer_skill', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('engineer_skill_id')->constrained('engineer_skills')->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['engineer_skill_id', 'asset_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_category_engineer_skill');
        Schema::dropIfExists('engineer_skill_ticket_subcategory');
        Schema::dropIfExists('engineer_skill_service');
        Schema::dropIfExists('engineer_skill_user');
        Schema::dropIfExists('engineer_skills');
    }
};
