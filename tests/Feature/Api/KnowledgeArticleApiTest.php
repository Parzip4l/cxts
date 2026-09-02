<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\KnowledgeArticle;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_create_published_knowledge_article_linked_to_ticket_and_problem(): void
    {
        $department = Department::query()->create([
            'code' => 'DEP-KBA',
            'name' => 'Knowledge Department',
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'email' => 'knowledge.supervisor@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'supervisor',
            'department_id' => $department->id,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'is_active' => true,
        ]);

        $status = TicketStatus::query()->create([
            'code' => 'COMPLETED',
            'name' => 'Completed',
            'is_closed' => true,
            'is_active' => true,
        ]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TCK-KBA-0001',
            'title' => 'VPN repeatedly disconnects',
            'description' => 'Requester loses VPN connection every few minutes.',
            'process_type' => Ticket::PROCESS_TYPE_INCIDENT,
            'requester_department_id' => $department->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $status->id,
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $problem = Problem::query()->create([
            'problem_number' => 'PRB-KBA-0001',
            'title' => 'VPN client instability',
            'description' => 'Recurring VPN incidents with the same client build.',
            'status' => Problem::STATUS_INVESTIGATING,
            'owner_user_id' => $supervisor->id,
            'ticket_priority_id' => $priority->id,
            'symptom' => 'VPN disconnects repeatedly.',
            'root_cause' => 'Client build regression.',
            'workaround' => 'Rollback to previous client.',
            'permanent_fix' => 'Deploy fixed client build.',
            'is_known_error' => true,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $supervisor->email,
            'password' => 'secret123',
        ])->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/knowledge-articles', [
                'title' => 'VPN disconnect workaround',
                'article_type' => KnowledgeArticle::TYPE_KNOWN_ERROR,
                'category' => 'Network',
                'status' => KnowledgeArticle::STATUS_PUBLISHED,
                'owner_user_id' => $supervisor->id,
                'summary' => 'Rollback VPN client when repeated disconnect appears.',
                'content' => 'Identify affected VPN client version, rollback, then monitor for recurrence.',
                'ticket_ids' => [$ticket->id],
                'problem_ids' => [$problem->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'VPN disconnect workaround')
            ->assertJsonPath('data.article_type', KnowledgeArticle::TYPE_KNOWN_ERROR)
            ->assertJsonPath('data.status', KnowledgeArticle::STATUS_PUBLISHED)
            ->assertJsonPath('data.ticket_count', 1)
            ->assertJsonPath('data.problem_count', 1)
            ->assertJsonPath('data.tickets.0.id', $ticket->id)
            ->assertJsonPath('data.problems.0.id', $problem->id);

        $articleId = (int) $response->json('data.id');

        $this->assertDatabaseHas('knowledge_articles', [
            'id' => $articleId,
            'article_type' => KnowledgeArticle::TYPE_KNOWN_ERROR,
            'status' => KnowledgeArticle::STATUS_PUBLISHED,
            'owner_user_id' => $supervisor->id,
        ]);

        $this->assertNotNull(KnowledgeArticle::query()->find($articleId)?->published_at);
        $this->assertDatabaseHas('knowledge_article_ticket', [
            'knowledge_article_id' => $articleId,
            'ticket_id' => $ticket->id,
        ]);
        $this->assertDatabaseHas('knowledge_article_problem', [
            'knowledge_article_id' => $articleId,
            'problem_id' => $problem->id,
        ]);
    }
}
