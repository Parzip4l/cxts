<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->date('work_date');
            $table->string('status', 30)->default('assigned');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
            $table->index('work_date');
            $table->index('shift_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_schedules');
    }
};
