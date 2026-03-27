<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_subcategory_id')->constrained('ticket_subcategories')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ticket_subcategory_id', 'code'], 'ticket_detail_subcategories_parent_code_unique');
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('ticket_detail_subcategory_id')
                ->nullable()
                ->after('ticket_subcategory_id')
                ->constrained('ticket_detail_subcategories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ticket_detail_subcategory_id');
        });

        Schema::dropIfExists('ticket_detail_subcategories');
    }
};
