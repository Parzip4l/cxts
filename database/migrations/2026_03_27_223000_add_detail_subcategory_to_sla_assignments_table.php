<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sla_policy_assignments', function (Blueprint $table): void {
            $table->foreignId('detail_subcategory_id')
                ->nullable()
                ->after('subcategory_id')
                ->constrained('ticket_detail_subcategories')
                ->nullOnDelete();

            $table->index(
                ['ticket_type', 'category_id', 'subcategory_id', 'detail_subcategory_id'],
                'sla_assignments_type_category_detail_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sla_policy_assignments', function (Blueprint $table): void {
            $table->dropIndex('sla_assignments_type_category_detail_idx');
            $table->dropConstrainedForeignId('detail_subcategory_id');
        });
    }
};
