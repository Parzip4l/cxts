<?php

namespace App\Modules\Dashboards\Operations\Web;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\Ticket;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Dashboards\Operations\OperationsDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsDashboardController extends Controller
{
    public function __construct(private readonly OperationsDashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $user = $request->user();

        return view('modules.dashboard.operations.index', [
            'filters' => $filters,
            'overview' => $this->dashboardService->overview($user, $filters),
            'slaPerformance' => $this->dashboardService->slaPerformance($user, $filters),
            'engineerEffectiveness' => $this->dashboardService->engineerEffectiveness($user, $filters),
            'myPerformance' => $user?->role === 'engineer' ? $this->dashboardService->myEngineerPerformance($user, $filters) : null,
            'isOpsRole' => in_array($user?->role, ['super_admin', 'operational_admin', 'supervisor'], true),
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function slaPerformance(Request $request): View
    {
        $filters = $this->filters($request);

        return view('modules.dashboard.operations.sla-performance', [
            'filters' => $filters,
            'data' => $this->dashboardService->slaPerformance($request->user(), $filters),
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function engineerEffectiveness(Request $request): View
    {
        $filters = $this->filters($request);

        return view('modules.dashboard.operations.engineer-effectiveness', [
            'filters' => $filters,
            'data' => $this->dashboardService->engineerEffectiveness($request->user(), $filters),
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function myPerformance(Request $request): View
    {
        $filters = $this->filters($request);

        return view('modules.dashboard.operations.my-performance', [
            'filters' => $filters,
            'data' => $this->dashboardService->myEngineerPerformance($request->user(), $filters),
            'filterOptions' => $this->filterOptions(),
        ]);
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

    private function filterOptions(): array
    {
        return [
            'categoryOptions' => TicketCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subcategoryOptions' => TicketSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'detailSubcategoryOptions' => TicketDetailSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_subcategory_id']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name']),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'approvalStatusOptions' => [
                Ticket::APPROVAL_STATUS_NOT_REQUIRED => 'Not Required',
                Ticket::APPROVAL_STATUS_PENDING => 'Pending',
                Ticket::APPROVAL_STATUS_APPROVED => 'Approved',
                Ticket::APPROVAL_STATUS_REJECTED => 'Rejected',
            ],
        ];
    }
}
