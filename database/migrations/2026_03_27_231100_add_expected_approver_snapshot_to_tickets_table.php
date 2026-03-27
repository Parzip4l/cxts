<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('expected_approver_id')->nullable()->after('approval_requested_at')->constrained('users')->nullOnDelete();
            $table->string('expected_approver_name_snapshot')->nullable()->after('expected_approver_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('expected_approver_id');
            $table->dropColumn('expected_approver_name_snapshot');
        });
    }
};
