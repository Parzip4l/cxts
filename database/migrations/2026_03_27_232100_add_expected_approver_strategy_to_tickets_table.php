<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->string('expected_approver_strategy', 50)->nullable()->after('expected_approver_name_snapshot');
            $table->string('expected_approver_role_code', 50)->nullable()->after('expected_approver_strategy');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn(['expected_approver_strategy', 'expected_approver_role_code']);
        });
    }
};
