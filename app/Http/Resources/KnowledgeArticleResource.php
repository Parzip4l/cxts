<?php

namespace App\Http\Resources;

use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_number' => $this->article_number,
            'title' => $this->title,
            'article_type' => $this->article_type,
            'article_type_label' => KnowledgeArticle::typeOptions()[$this->article_type] ?? $this->article_type,
            'category' => $this->category,
            'status' => $this->status,
            'status_label' => KnowledgeArticle::statusOptions()[$this->status] ?? $this->status,
            'owner_user_id' => $this->owner_user_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'summary' => $this->summary,
            'content' => $this->content,
            'published_at' => $this->published_at,
            'ticket_count' => $this->whenCounted('tickets'),
            'problem_count' => $this->whenCounted('problems'),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'problems' => ProblemResource::collection($this->whenLoaded('problems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
