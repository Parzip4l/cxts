<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 120);
            $table->string('action', 160);
            $table->string('route_name', 160)->nullable();
            $table->string('method', 10);
            $table->string('path', 255);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('subject_type', 160)->nullable();
            $table->string('subject_id', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
            $table->index(['route_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
