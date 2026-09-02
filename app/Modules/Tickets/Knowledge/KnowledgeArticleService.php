<?php

namespace App\Modules\Tickets\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class KnowledgeArticleService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): KnowledgeArticle
    {
        return DB::transaction(function () use ($data, $actor): KnowledgeArticle {
            $article = KnowledgeArticle::query()->create([
                ...$this->preparePayload($data),
                'article_number' => $this->generateArticleNumber(),
                'created_by_id' => $actor?->id,
                'updated_by_id' => $actor?->id,
            ]);

            $article->tickets()->sync($this->ids($data, 'ticket_ids'));
            $article->problems()->sync($this->ids($data, 'problem_ids'));

            return $article->fresh($this->relations())->loadCount(['tickets', 'problems']);
        });
    }

    public function update(KnowledgeArticle $article, array $data, ?User $actor = null): KnowledgeArticle
    {
        return DB::transaction(function () use ($article, $data, $actor): KnowledgeArticle {
            $article->update([
                ...$this->preparePayload($data, $article),
                'updated_by_id' => $actor?->id,
            ]);

            $article->tickets()->sync($this->ids($data, 'ticket_ids'));
            $article->problems()->sync($this->ids($data, 'problem_ids'));

            return $article->fresh($this->relations())->loadCount(['tickets', 'problems']);
        });
    }

    public function delete(KnowledgeArticle $article): void
    {
        $article->delete();
    }

    private function query(array $filters = []): Builder
    {
        return KnowledgeArticle::query()
            ->with($this->relations())
            ->withCount(['tickets', 'problems'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('article_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($filters['article_type'] ?? null, fn (Builder $query, string $type) => $query->where('article_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category));
    }

    private function relations(): array
    {
        return [
            'owner:id,name',
            'tickets:id,ticket_number,title,process_type,ticket_status_id',
            'tickets.status:id,name,code',
            'problems:id,problem_number,title,status',
        ];
    }

    private function preparePayload(array $data, ?KnowledgeArticle $article = null): array
    {
        $payload = Arr::except($data, ['ticket_ids', 'problem_ids']);
        if (($payload['status'] ?? null) === KnowledgeArticle::STATUS_PUBLISHED && empty($payload['published_at'])) {
            $payload['published_at'] = $article?->published_at ?? now();
        }

        if (($payload['status'] ?? null) !== KnowledgeArticle::STATUS_PUBLISHED) {
            $payload['published_at'] = null;
        }

        return $payload;
    }

    private function ids(array $data, string $key): array
    {
        return collect($data[$key] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function generateArticleNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $base = "KBA-{$datePrefix}";
        $lastNumber = KnowledgeArticle::query()
            ->where('article_number', 'like', "{$base}-%")
            ->orderByDesc('article_number')
            ->value('article_number');

        $lastSequence = 0;
        if (is_string($lastNumber)) {
            $segments = explode('-', $lastNumber);
            $lastSequence = (int) end($segments);
        }

        return $base.'-'.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
