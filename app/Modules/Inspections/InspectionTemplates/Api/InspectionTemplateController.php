<?php

namespace App\Modules\Inspections\InspectionTemplates\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionTemplateResource;
use App\Models\InspectionTemplate;
use App\Modules\Inspections\InspectionTemplates\InspectionTemplateService;
use App\Modules\Inspections\InspectionTemplates\Requests\StoreInspectionTemplateRequest;
use App\Modules\Inspections\InspectionTemplates\Requests\UpdateInspectionTemplateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspectionTemplateController extends Controller
{
    public function __construct(private readonly InspectionTemplateService $inspectionTemplateService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'asset_category_id' => $request->input('asset_category_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $templates = $this->inspectionTemplateService->paginate($filters, (int) $request->input('per_page', 15));

        return InspectionTemplateResource::collection($templates);
    }

    public function store(StoreInspectionTemplateRequest $request): JsonResponse
    {
        return (new InspectionTemplateResource(
            $this->inspectionTemplateService->create($request->validated(), $request->user())
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function show(InspectionTemplate $inspectionTemplate): InspectionTemplateResource
    {
        return new InspectionTemplateResource($inspectionTemplate->load(['assetCategory:id,name', 'items']));
    }

    public function update(UpdateInspectionTemplateRequest $request, InspectionTemplate $inspectionTemplate): InspectionTemplateResource
    {
        return new InspectionTemplateResource(
            $this->inspectionTemplateService->update($inspectionTemplate, $request->validated(), $request->user())
        );
    }

    public function destroy(InspectionTemplate $inspectionTemplate): JsonResponse
    {
        $this->inspectionTemplateService->delete($inspectionTemplate);

        return response()->json(['message' => 'Inspection template deleted.']);
    }
}
