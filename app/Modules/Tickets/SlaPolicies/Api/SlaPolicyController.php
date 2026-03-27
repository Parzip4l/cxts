<?php

namespace App\Modules\Tickets\SlaPolicies\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlaPolicyResource;
use App\Models\SlaPolicy;
use App\Modules\Tickets\SlaPolicies\Requests\StoreSlaPolicyRequest;
use App\Modules\Tickets\SlaPolicies\Requests\UpdateSlaPolicyRequest;
use App\Modules\Tickets\SlaPolicies\SlaPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function __construct(private readonly SlaPolicyService $slaPolicyService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return SlaPolicyResource::collection(
            $this->slaPolicyService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(StoreSlaPolicyRequest $request): SlaPolicyResource
    {
        return new SlaPolicyResource($this->slaPolicyService->create($request->validated()));
    }

    public function show(SlaPolicy $slaPolicy): SlaPolicyResource
    {
        return new SlaPolicyResource($slaPolicy);
    }

    public function update(UpdateSlaPolicyRequest $request, SlaPolicy $slaPolicy): SlaPolicyResource
    {
        return new SlaPolicyResource($this->slaPolicyService->update($slaPolicy, $request->validated()));
    }

    public function destroy(SlaPolicy $slaPolicy): JsonResponse
    {
        $this->slaPolicyService->delete($slaPolicy);

        return response()->json(['message' => 'SLA policy deleted.']);
    }
}
