<?php

namespace App\Http\Resources;

use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'problem_number' => $this->problem_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => Problem::statusOptions()[$this->status] ?? $this->status,
            'owner_user_id' => $this->owner_user_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'ticket_priority_id' => $this->ticket_priority_id,
            'ticket_priority_name' => $this->whenLoaded('priority', fn () => $this->priority?->name),
            'symptom' => $this->symptom,
            'root_cause' => $this->root_cause,
            'workaround' => $this->workaround,
            'permanent_fix' => $this->permanent_fix,
            'is_known_error' => (bool) $this->is_known_error,
            'action_item' => $this->action_item,
            'target_resolution_at' => $this->target_resolution_at,
            'resolved_at' => $this->resolved_at,
            'ticket_count' => $this->whenCounted('tickets'),
            'knowledge_article_count' => $this->whenCounted('knowledgeArticles'),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'knowledge_articles' => KnowledgeArticleResource::collection($this->whenLoaded('knowledgeArticles')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
