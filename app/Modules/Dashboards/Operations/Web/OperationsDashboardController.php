<?php

namespace App\Modules\Dashboards\Operations\Web;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\Ticket;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Dashboards\Operations\OperationsDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class OperationsDashboardController extends Controller
{
    public function __construct(private readonly OperationsDashboardService $dashboardService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->filters($request);
        $user = $request->user();

        if (! $user?->hasPermission('dashboard.view_ops')) {
            if ($user?->role === 'requester') {
                return redirect()->route('tickets.index');
            }

            if ($user?->role === 'engineer') {
                return redirect()->route('engineer-tasks.index');
            }

            if ($user?->role === 'inspection_officer') {
                return redirect()->route('inspections.index');
            }
        }

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

    public function report(Request $request): View
    {
        $filters = $this->filters($request);

        return view('modules.dashboard.operations.report', [
            'filters' => $filters,
            'data' => $this->dashboardService->executiveReport($request->user(), $filters),
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function exportReport(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $data = $this->dashboardService->executiveReport($request->user(), $filters);
        $filename = 'cxts-executive-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');

            $writeSection = function (string $title) use ($handle): void {
                fputcsv($handle, []);
                fputcsv($handle, [$title]);
            };

            $current = $data['current'];
            $summary = $data['executive_summary'];

            fputcsv($handle, ['CXTS Executive Report']);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Period From', $current['period']['date_from']]);
            fputcsv($handle, ['Period To', $current['period']['date_to']]);
            fputcsv($handle, ['Headline', $summary['headline']]);
            fputcsv($handle, ['Tone', $summary['tone']]);
            fputcsv($handle, ['Note', $summary['note']]);

            $writeSection('Snapshot Metrics');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Tickets', $current['summary']['total_tickets']]);
            fputcsv($handle, ['Completion Rate', $current['derived']['completion_rate'] . '%']);
            fputcsv($handle, ['Response SLA', $current['sla']['response']['compliance_rate'] . '%']);
            fputcsv($handle, ['Resolution SLA', $current['sla']['resolution']['compliance_rate'] . '%']);
            fputcsv($handle, ['MTTR (Minutes)', $current['summary']['mttr_minutes']]);
            fputcsv($handle, ['Engineer Effectiveness', $current['engineer']['avg_effectiveness_score']]);
            fputcsv($handle, ['Abnormal Inspections', $current['inspection']['abnormal_inspections']]);

            $writeSection('Highlights');
            fputcsv($handle, ['Title', 'Tone', 'Message']);
            foreach ($summary['highlights'] as $highlight) {
                fputcsv($handle, [$highlight['title'], $highlight['tone'], $highlight['message']]);
            }

            $writeSection('Recommended Action Plan');
            fputcsv($handle, ['Priority', 'Owner', 'Target', 'Title', 'Message']);
            foreach ($data['action_plan'] ?? [] as $action) {
                fputcsv($handle, [$action['priority'], $action['owner'], $action['timeframe'], $action['title'], $action['message']]);
            }

            $writeSection('Top Risks');
            fputcsv($handle, ['Severity', 'Title', 'Message']);
            foreach ($data['top_risks'] ?? [] as $risk) {
                fputcsv($handle, [$risk['severity'], $risk['title'], $risk['message']]);
            }

            $writeSection('Top Improvement Areas');
            fputcsv($handle, ['Priority', 'Title', 'Message']);
            foreach ($data['top_improvement_areas'] ?? [] as $area) {
                fputcsv($handle, [$area['priority'], $area['title'], $area['message']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
