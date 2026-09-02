<?php

namespace App\Modules\Tickets\Problems\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProblemResource;
use App\Models\Problem;
use App\Modules\Tickets\Problems\ProblemService;
use App\Modules\Tickets\Problems\Requests\ProblemRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function __construct(private readonly ProblemService $problemService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'owner_user_id' => $request->input('owner_user_id'),
        ];

        if ($request->has('is_known_error') && $request->input('is_known_error') !== '') {
            $filters['is_known_error'] = (bool) $request->input('is_known_error');
        }

        return ProblemResource::collection(
            $this->problemService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(ProblemRequest $request): JsonResponse
    {
        return (new ProblemResource($this->problemService->create($request->validated(), $request->user())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Problem $problem): ProblemResource
    {
        return new ProblemResource(
            $problem
                ->load(['owner:id,name', 'priority:id,name', 'tickets.status:id,name,code', 'tickets.priority:id,name', 'knowledgeArticles'])
                ->loadCount(['tickets', 'knowledgeArticles'])
        );
    }

    public function update(ProblemRequest $request, Problem $problem): ProblemResource
    {
        return new ProblemResource($this->problemService->update($problem, $request->validated(), $request->user()));
    }

    public function destroy(Problem $problem): JsonResponse
    {
        $this->problemService->delete($problem);

        return response()->json(['message' => 'Problem deleted.']);
    }
}
