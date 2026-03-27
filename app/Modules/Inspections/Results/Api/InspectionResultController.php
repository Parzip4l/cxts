<?php

namespace App\Modules\Inspections\Results\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionResource;
use App\Models\Inspection;
use App\Modules\Inspections\Inspections\InspectionService;
use Illuminate\Http\Request;

class InspectionResultController extends Controller
{
    public function __construct(private readonly InspectionService $inspectionService)
    {
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'final_result' => $request->input('final_result'),
            'inspection_officer_id' => $request->input('inspection_officer_id'),
            'inspection_date_from' => $request->input('inspection_date_from'),
            'inspection_date_to' => $request->input('inspection_date_to'),
            'has_ticket' => $request->input('has_ticket'),
        ];

        $inspections = $this->inspectionService->paginateInspectionResults(
            actor: $actor,
            filters: $filters,
            perPage: (int) $request->input('per_page', 15),
        );

        return InspectionResource::collection($inspections)->additional([
            'summary' => $this->inspectionService->summarizeInspectionResults($actor, $filters),
        ]);
    }

    public function show(Request $request, Inspection $inspection): InspectionResource
    {
        return new InspectionResource(
            $this->inspectionService->resolveInspectionResultDetail($inspection, $request->user())
        );
    }
}

