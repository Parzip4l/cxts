<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->foreignId('approver_user_id')->nullable()->after('allow_direct_assignment')->constrained('users')->nullOnDelete();
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->foreignId('approver_user_id')->nullable()->after('allow_direct_assignment')->constrained('users')->nullOnDelete();
        });

        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->foreignId('approver_user_id')->nullable()->after('allow_direct_assignment')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approver_user_id');
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approver_user_id');
        });

        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approver_user_id');
        });
    }
};
