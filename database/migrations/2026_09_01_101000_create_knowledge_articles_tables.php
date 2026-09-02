<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('article_number', 50)->unique();
            $table->string('title', 200);
            $table->string('article_type', 40)->default('troubleshooting');
            $table->string('category', 100)->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'article_type']);
            $table->index(['category', 'status']);
            $table->index(['owner_user_id', 'status']);
        });

        Schema::create('knowledge_article_ticket', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'ticket_id'], 'knowledge_ticket_unique');
            $table->index(['ticket_id', 'knowledge_article_id']);
        });

        Schema::create('knowledge_article_problem', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('problem_id')->constrained('problems')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['knowledge_article_id', 'problem_id'], 'knowledge_problem_unique');
            $table->index(['problem_id', 'knowledge_article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_problem');
        Schema::dropIfExists('knowledge_article_ticket');
        Schema::dropIfExists('knowledge_articles');
    }
};
