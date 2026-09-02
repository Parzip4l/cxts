<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_number', 50)->unique();
            $table->string('source', 100);
            $table->string('severity', 30)->default('medium');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('message', 500);
            $table->text('details')->nullable();
            $table->timestamp('occurred_at');
            $table->string('status', 30)->default('open');
            $table->string('deduplication_key', 160)->nullable();
            $table->unsignedInteger('duplicate_count')->default(1);
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('converted_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['source', 'occurred_at']);
            $table->index(['service_id', 'asset_id']);
            $table->index(['deduplication_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_events');
    }
};
