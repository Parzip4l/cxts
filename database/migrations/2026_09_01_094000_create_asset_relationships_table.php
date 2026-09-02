<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_relationships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('related_asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('relationship_type', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'related_asset_id', 'relationship_type'], 'asset_relationship_unique');
            $table->index(['relationship_type', 'related_asset_id'], 'asset_relationship_type_related_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_relationships');
    }
};
