<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->boolean('is_requestable')->default(true)->after('is_active');
            $table->boolean('default_request_approval_required')->nullable()->after('is_requestable');
            $table->foreignId('default_request_sla_policy_id')
                ->nullable()
                ->after('default_request_approval_required')
                ->constrained('sla_policies')
                ->nullOnDelete();
            $table->string('fulfillment_team_name', 100)->nullable()->after('default_request_sla_policy_id');
            $table->json('request_form_schema')->nullable()->after('fulfillment_team_name');

            $table->index(['is_requestable', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropIndex(['is_requestable', 'is_active']);
            $table->dropConstrainedForeignId('default_request_sla_policy_id');
            $table->dropColumn([
                'is_requestable',
                'default_request_approval_required',
                'fulfillment_team_name',
                'request_form_schema',
            ]);
        });
    }
};
