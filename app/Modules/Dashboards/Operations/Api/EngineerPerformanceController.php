<?php

namespace App\Modules\Dashboards\Operations\Api;

use App\Http\Controllers\Controller;
use App\Modules\Dashboards\Operations\OperationsDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngineerPerformanceController extends Controller
{
    public function __construct(private readonly OperationsDashboardService $dashboardService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->myEngineerPerformance($request->user(), [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'ticket_category_id' => $request->input('ticket_category_id'),
            'ticket_subcategory_id' => $request->input('ticket_subcategory_id'),
            'ticket_detail_subcategory_id' => $request->input('ticket_detail_subcategory_id'),
            'expected_approver_id' => $request->input('expected_approver_id'),
            'expected_approver_role_code' => $request->input('expected_approver_role_code'),
            'approval_status' => $request->input('approval_status'),
        ]));
    }
}
