<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('sla_policy_id')
                ->nullable()
                ->after('assigned_engineer_id')
                ->constrained('sla_policies')
                ->nullOnDelete();

            $table->string('sla_name_snapshot', 150)->nullable()->after('sla_policy_name');
            $table->timestamp('responded_at')->nullable()->after('response_due_at');
            $table->timestamp('breached_response_at')->nullable()->after('responded_at');
            $table->string('sla_status', 20)->nullable()->after('resolved_at');
            $table->timestamp('breached_resolution_at')->nullable()->after('sla_status');

            $table->index(['sla_policy_id', 'sla_status']);
            $table->index(['responded_at', 'resolved_at']);
            $table->index(['breached_response_at', 'breached_resolution_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['sla_policy_id', 'sla_status']);
            $table->dropIndex(['responded_at', 'resolved_at']);
            $table->dropIndex(['breached_response_at', 'breached_resolution_at']);
            $table->dropConstrainedForeignId('sla_policy_id');
            $table->dropColumn([
                'sla_name_snapshot',
                'responded_at',
                'breached_response_at',
                'sla_status',
                'breached_resolution_at',
            ]);
        });
    }
};
