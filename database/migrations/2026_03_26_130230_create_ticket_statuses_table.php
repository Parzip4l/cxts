<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_open')->default(true);
            $table->boolean('is_in_progress')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_open', 'is_in_progress', 'is_closed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_statuses');
    }
};
