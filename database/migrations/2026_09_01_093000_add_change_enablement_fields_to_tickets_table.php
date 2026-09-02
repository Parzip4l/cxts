<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->text('change_reason')->nullable()->after('incident_resolution_code');
            $table->string('change_risk_level', 20)->nullable()->after('change_reason');
            $table->timestamp('change_planned_start_at')->nullable()->after('change_risk_level');
            $table->timestamp('change_planned_end_at')->nullable()->after('change_planned_start_at');
            $table->text('change_rollback_plan')->nullable()->after('change_planned_end_at');
            $table->text('change_affected_scope')->nullable()->after('change_rollback_plan');
            $table->string('change_review_result', 30)->nullable()->after('change_affected_scope');
            $table->text('change_review_notes')->nullable()->after('change_review_result');

            $table->index(['process_type', 'change_planned_start_at'], 'tickets_process_change_start_idx');
            $table->index(['change_risk_level', 'change_planned_start_at'], 'tickets_change_risk_start_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex('tickets_process_change_start_idx');
            $table->dropIndex('tickets_change_risk_start_idx');
            $table->dropColumn([
                'change_reason',
                'change_risk_level',
                'change_planned_start_at',
                'change_planned_end_at',
                'change_rollback_plan',
                'change_affected_scope',
                'change_review_result',
                'change_review_notes',
            ]);
        });
    }
};
