<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sla_policies', function (Blueprint $table): void {
            $table->boolean('escalate_on_warning')->default(false)->after('working_hours_id');
            $table->boolean('escalate_on_breach')->default(true)->after('escalate_on_warning');
            $table->string('escalation_role_code', 60)->nullable()->after('escalate_on_breach');
            $table->string('escalation_note', 500)->nullable()->after('escalation_role_code');
        });

        Schema::create('sla_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->string('event_type', 50);
            $table->string('target', 30)->nullable();
            $table->timestamp('event_at');
            $table->timestamp('due_at')->nullable();
            $table->unsignedTinyInteger('threshold_percentage')->nullable();
            $table->string('old_sla_status', 30)->nullable();
            $table->string('new_sla_status', 30)->nullable();
            $table->string('escalation_role_code', 60)->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'event_type', 'target'], 'sla_event_once_per_target');
            $table->index(['event_type', 'event_at']);
            $table->index(['sla_policy_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_events');

        Schema::table('sla_policies', function (Blueprint $table): void {
            $table->dropColumn([
                'escalate_on_warning',
                'escalate_on_breach',
                'escalation_role_code',
                'escalation_note',
            ]);
        });
    }
};
