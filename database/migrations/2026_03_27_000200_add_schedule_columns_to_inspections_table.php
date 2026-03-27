<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table): void {
            $table->foreignId('scheduled_by_id')
                ->nullable()
                ->after('inspection_officer_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('schedule_type', 20)
                ->default('none')
                ->after('status');

            $table->unsignedSmallInteger('schedule_interval')
                ->default(1)
                ->after('schedule_type');

            $table->json('schedule_weekdays')
                ->nullable()
                ->after('schedule_interval');

            $table->date('schedule_next_date')
                ->nullable()
                ->after('inspection_date');

            $table->foreignId('parent_inspection_id')
                ->nullable()
                ->after('summary_notes')
                ->constrained('inspections')
                ->nullOnDelete();

            $table->index(['inspection_officer_id', 'status', 'inspection_date'], 'inspections_officer_status_date_idx');
            $table->index(['schedule_type', 'schedule_next_date'], 'inspections_schedule_type_next_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table): void {
            $table->dropIndex('inspections_officer_status_date_idx');
            $table->dropIndex('inspections_schedule_type_next_date_idx');
            $table->dropConstrainedForeignId('parent_inspection_id');
            $table->dropColumn('schedule_next_date');
            $table->dropColumn('schedule_weekdays');
            $table->dropColumn('schedule_interval');
            $table->dropColumn('schedule_type');
            $table->dropConstrainedForeignId('scheduled_by_id');
        });
    }
};
