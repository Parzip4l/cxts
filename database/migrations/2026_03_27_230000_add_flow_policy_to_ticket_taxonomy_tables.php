<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->boolean('requires_approval')->default(false)->after('description');
            $table->boolean('allow_direct_assignment')->default(true)->after('requires_approval');
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->boolean('requires_approval')->nullable()->after('description');
            $table->boolean('allow_direct_assignment')->nullable()->after('requires_approval');
        });

        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->boolean('requires_approval')->nullable()->after('description');
            $table->boolean('allow_direct_assignment')->nullable()->after('requires_approval');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_detail_subcategories', function (Blueprint $table): void {
            $table->dropColumn(['requires_approval', 'allow_direct_assignment']);
        });

        Schema::table('ticket_subcategories', function (Blueprint $table): void {
            $table->dropColumn(['requires_approval', 'allow_direct_assignment']);
        });

        Schema::table('ticket_categories', function (Blueprint $table): void {
            $table->dropColumn(['requires_approval', 'allow_direct_assignment']);
        });
    }
};
