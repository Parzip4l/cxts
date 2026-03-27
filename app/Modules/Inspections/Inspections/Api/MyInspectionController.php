<?php

namespace App\Modules\Inspections\Inspections\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetLocationResource;
use App\Http\Resources\AssetResource;
use App\Http\Resources\InspectionEvidenceResource;
use App\Http\Resources\InspectionResource;
use App\Http\Resources\InspectionTemplateResource;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Modules\Inspections\Inspections\InspectionService;
use App\Modules\Inspections\Inspections\Requests\StoreInspectionEvidenceRequest;
use App\Modules\Inspections\Inspections\Requests\StoreInspectionRequest;
use App\Modules\Inspections\Inspections\Requests\SubmitInspectionRequest;
use App\Modules\Inspections\Inspections\Requests\UpdateInspectionItemsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyInspectionController extends Controller
{
    public function __construct(private readonly InspectionService $inspectionService)
    {
    }

    public function index(Request $request)
    {
        $inspections = $this->inspectionService->paginateMyInspections(
            officer: $request->user(),
            filters: [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'inspection_date' => $request->input('inspection_date'),
                'due_only' => true,
            ],
            perPage: (int) $request->input('per_page', 15),
        );

        return InspectionResource::collection($inspections);
    }

    public function templates(Request $request)
    {
        $templates = InspectionTemplate::query()
            ->where('is_active', true)
            ->with('assetCategory:id,name')
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 30));

        return InspectionTemplateResource::collection($templates);
    }

    public function assets(Request $request)
    {
        $assets = Asset::query()
            ->where('is_active', true)
            ->with([
                'category:id,name',
                'service:id,name',
                'ownerDepartment:id,name',
                'vendor:id,name',
                'location:id,name',
                'status:id,name',
            ])
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 30));

        return AssetResource::collection($assets);
    }

    public function locations(Request $request)
    {
        $locations = AssetLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 50));

        return AssetLocationResource::collection($locations);
    }

    public function store(StoreInspectionRequest $request): JsonResponse
    {
        return (new InspectionResource(
            $this->inspectionService->createForOfficer($request->validated(), $request->user())
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Inspection $inspection): InspectionResource
    {
        $this->inspectionService->ensureOwnedByOfficer($inspection, $request->user());

        return new InspectionResource($inspection->load($this->inspectionService->inspectionRelations()));
    }

    public function updateItems(UpdateInspectionItemsRequest $request, Inspection $inspection): InspectionResource
    {
        return new InspectionResource(
            $this->inspectionService->updateItems($inspection, $request->user(), $request->validated('items'))
        );
    }

    public function submit(SubmitInspectionRequest $request, Inspection $inspection): InspectionResource
    {
        return new InspectionResource(
            $this->inspectionService->submit(
                inspection: $inspection,
                officer: $request->user(),
                finalResult: $request->validated('final_result'),
                summaryNotes: $request->validated('summary_notes'),
                supportingFiles: $request->file('supporting_files', []),
            )
        );
    }

    public function storeEvidence(StoreInspectionEvidenceRequest $request, Inspection $inspection): JsonResponse
    {
        return (new InspectionEvidenceResource(
            $this->inspectionService->addEvidence(
                inspection: $inspection,
                officer: $request->user(),
                file: $request->file('file'),
                notes: $request->validated('notes'),
                inspectionItemId: $request->validated('inspection_item_id'),
            )
        ))
            ->response()
            ->setStatusCode(201);
    }
}
