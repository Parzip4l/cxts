<?php

namespace App\Modules\Tickets\SlaPolicyAssignments\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlaPolicyAssignmentResource;
use App\Models\SlaPolicyAssignment;
use App\Modules\Tickets\SlaPolicyAssignments\Requests\StoreSlaPolicyAssignmentRequest;
use App\Modules\Tickets\SlaPolicyAssignments\Requests\UpdateSlaPolicyAssignmentRequest;
use App\Modules\Tickets\SlaPolicyAssignments\SlaPolicyAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlaPolicyAssignmentController extends Controller
{
    public function __construct(private readonly SlaPolicyAssignmentService $slaPolicyAssignmentService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'sla_policy_id' => $request->input('sla_policy_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return SlaPolicyAssignmentResource::collection(
            $this->slaPolicyAssignmentService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(StoreSlaPolicyAssignmentRequest $request): SlaPolicyAssignmentResource
    {
        return new SlaPolicyAssignmentResource($this->slaPolicyAssignmentService->create($request->validated()));
    }

    public function show(SlaPolicyAssignment $slaPolicyAssignment): SlaPolicyAssignmentResource
    {
        return new SlaPolicyAssignmentResource($slaPolicyAssignment->load([
            'policy:id,name',
            'category:id,name',
            'subcategory:id,name,ticket_category_id',
            'detailSubcategory:id,name,ticket_subcategory_id',
            'serviceItem:id,name',
            'priority:id,name,code',
        ]));
    }

    public function update(UpdateSlaPolicyAssignmentRequest $request, SlaPolicyAssignment $slaPolicyAssignment): SlaPolicyAssignmentResource
    {
        return new SlaPolicyAssignmentResource(
            $this->slaPolicyAssignmentService->update($slaPolicyAssignment, $request->validated())
        );
    }

    public function destroy(SlaPolicyAssignment $slaPolicyAssignment): JsonResponse
    {
        $this->slaPolicyAssignmentService->delete($slaPolicyAssignment);

        return response()->json(['message' => 'SLA policy assignment deleted.']);
    }
}
