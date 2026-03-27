<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->boolean('requires_approval')->default(false)->after('assigned_engineer_id');
            $table->boolean('allow_direct_assignment')->default(true)->after('requires_approval');
            $table->string('approval_status', 30)->default('not_required')->after('allow_direct_assignment');
            $table->timestamp('approval_requested_at')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approval_requested_at');
            $table->foreignId('approved_by_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by_id');
            $table->foreignId('rejected_by_id')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('approval_notes')->nullable()->after('rejected_by_id');
            $table->timestamp('assignment_ready_at')->nullable()->after('approval_notes');
            $table->foreignId('assignment_ready_by_id')->nullable()->after('assignment_ready_at')->constrained('users')->nullOnDelete();
            $table->string('flow_policy_source', 50)->nullable()->after('assignment_ready_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assignment_ready_by_id');
            $table->dropConstrainedForeignId('rejected_by_id');
            $table->dropConstrainedForeignId('approved_by_id');
            $table->dropColumn([
                'requires_approval',
                'allow_direct_assignment',
                'approval_status',
                'approval_requested_at',
                'approved_at',
                'rejected_at',
                'approval_notes',
                'assignment_ready_at',
                'flow_policy_source',
            ]);
        });
    }
};
