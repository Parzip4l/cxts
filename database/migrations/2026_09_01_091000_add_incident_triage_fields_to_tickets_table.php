<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->string('incident_detection_source', 50)->nullable()->after('process_type');
            $table->boolean('is_major_incident')->default(false)->after('incident_detection_source');
            $table->unsignedInteger('affected_users_count')->nullable()->after('is_major_incident');
            $table->text('service_impact_note')->nullable()->after('affected_users_count');
            $table->string('incident_resolution_code', 50)->nullable()->after('service_impact_note');

            $table->index(['is_major_incident', 'created_at']);
            $table->index(['incident_detection_source', 'created_at']);
            $table->index(['incident_resolution_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['is_major_incident', 'created_at']);
            $table->dropIndex(['incident_detection_source', 'created_at']);
            $table->dropIndex(['incident_resolution_code', 'created_at']);
            $table->dropColumn([
                'incident_detection_source',
                'is_major_incident',
                'affected_users_count',
                'service_impact_note',
                'incident_resolution_code',
            ]);
        });
    }
};
