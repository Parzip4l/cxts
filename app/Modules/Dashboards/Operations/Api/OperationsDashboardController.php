<?php

namespace App\Modules\Dashboards\Operations\Api;

use App\Http\Controllers\Controller;
use App\Modules\Dashboards\Operations\OperationsDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationsDashboardController extends Controller
{
    public function __construct(private readonly OperationsDashboardService $dashboardService)
    {
    }

    public function overview(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->overview($request->user(), $this->filters($request)));
    }

    public function slaPerformance(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->slaPerformance($request->user(), $this->filters($request)));
    }

    public function engineerEffectiveness(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->engineerEffectiveness($request->user(), $this->filters($request)));
    }

    private function filters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'ticket_category_id' => $request->input('ticket_category_id'),
            'ticket_subcategory_id' => $request->input('ticket_subcategory_id'),
            'ticket_detail_subcategory_id' => $request->input('ticket_detail_subcategory_id'),
            'expected_approver_id' => $request->input('expected_approver_id'),
            'expected_approver_role_code' => $request->input('expected_approver_role_code'),
            'approval_status' => $request->input('approval_status'),
        ];
    }
}
