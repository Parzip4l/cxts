<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('inspection_id')
                ->nullable()
                ->after('asset_location_id')
                ->constrained('inspections')
                ->nullOnDelete();

            $table->unique('inspection_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropUnique(['inspection_id']);
            $table->dropConstrainedForeignId('inspection_id');
        });
    }
};
