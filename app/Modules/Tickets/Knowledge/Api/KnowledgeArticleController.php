<?php

namespace App\Modules\Tickets\Knowledge\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KnowledgeArticleResource;
use App\Models\KnowledgeArticle;
use App\Modules\Tickets\Knowledge\KnowledgeArticleService;
use App\Modules\Tickets\Knowledge\Requests\KnowledgeArticleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeArticleController extends Controller
{
    public function __construct(private readonly KnowledgeArticleService $knowledgeArticleService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'article_type' => $request->input('article_type'),
            'status' => $request->input('status'),
            'category' => $request->input('category'),
        ];

        return KnowledgeArticleResource::collection(
            $this->knowledgeArticleService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(KnowledgeArticleRequest $request): JsonResponse
    {
        return (new KnowledgeArticleResource($this->knowledgeArticleService->create($request->validated(), $request->user())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(KnowledgeArticle $knowledgeArticle): KnowledgeArticleResource
    {
        return new KnowledgeArticleResource(
            $knowledgeArticle
                ->load(['owner:id,name', 'tickets.status:id,name,code', 'tickets.priority:id,name', 'problems'])
                ->loadCount(['tickets', 'problems'])
        );
    }

    public function update(KnowledgeArticleRequest $request, KnowledgeArticle $knowledgeArticle): KnowledgeArticleResource
    {
        return new KnowledgeArticleResource($this->knowledgeArticleService->update($knowledgeArticle, $request->validated(), $request->user()));
    }

    public function destroy(KnowledgeArticle $knowledgeArticle): JsonResponse
    {
        $this->knowledgeArticleService->delete($knowledgeArticle);

        return response()->json(['message' => 'Knowledge article deleted.']);
    }
}
