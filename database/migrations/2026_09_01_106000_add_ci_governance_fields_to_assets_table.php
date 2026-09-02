<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->boolean('is_configuration_item')->default(false)->after('criticality');
            $table->string('ci_type', 60)->nullable()->after('is_configuration_item');
            $table->string('ci_lifecycle_state', 40)->nullable()->after('ci_type');
            $table->text('ci_governance_note')->nullable()->after('ci_lifecycle_state');

            $table->index(['is_configuration_item', 'ci_type']);
            $table->index(['ci_lifecycle_state', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->dropIndex(['is_configuration_item', 'ci_type']);
            $table->dropIndex(['ci_lifecycle_state', 'is_active']);
            $table->dropColumn([
                'is_configuration_item',
                'ci_type',
                'ci_lifecycle_state',
                'ci_governance_note',
            ]);
        });
    }
};
