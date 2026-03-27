<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->string('approver_strategy', 50)->nullable()->after('approver_user_id');
            $table->string('approver_role_code', 50)->nullable()->after('approver_strategy');
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->string('approver_strategy', 50)->nullable()->after('approver_user_id');
            $table->string('approver_role_code', 50)->nullable()->after('approver_strategy');
        });

        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->string('approver_strategy', 50)->nullable()->after('approver_user_id');
            $table->string('approver_role_code', 50)->nullable()->after('approver_strategy');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->dropColumn(['approver_strategy', 'approver_role_code']);
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->dropColumn(['approver_strategy', 'approver_role_code']);
        });

        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->dropColumn(['approver_strategy', 'approver_role_code']);
        });
    }
};
