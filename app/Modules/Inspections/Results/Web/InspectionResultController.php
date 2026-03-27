<?php

namespace App\Modules\Inspections\Results\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\User;
use App\Modules\Inspections\Inspections\InspectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionResultController extends Controller
{
    public function __construct(private readonly InspectionService $inspectionService)
    {
    }

    public function index(Request $request): View
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

        return view('modules.inspections.results.index', [
            'inspections' => $inspections,
            'filters' => $filters,
            'summary' => $this->inspectionService->summarizeInspectionResults($actor, $filters),
            'statusOptions' => [
                Inspection::STATUS_DRAFT,
                Inspection::STATUS_IN_PROGRESS,
                Inspection::STATUS_SUBMITTED,
            ],
            'finalResultOptions' => [
                Inspection::FINAL_RESULT_NORMAL,
                Inspection::FINAL_RESULT_ABNORMAL,
            ],
            'officerOptions' => User::query()
                ->where('role', 'inspection_officer')
                ->orderBy('name')
                ->get(['id', 'name']),
            'canOpenTicketDetail' => in_array($actor?->role, ['super_admin', 'operational_admin', 'supervisor'], true),
        ]);
    }

    public function show(Request $request, Inspection $inspection): View
    {
        $actor = $request->user();

        return view('modules.inspections.results.show', [
            'inspection' => $this->inspectionService->resolveInspectionResultDetail($inspection, $actor),
            'canOpenTicketDetail' => in_array($actor?->role, ['super_admin', 'operational_admin', 'supervisor'], true),
        ]);
    }
}

