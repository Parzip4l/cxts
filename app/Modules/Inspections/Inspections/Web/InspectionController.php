<?php

namespace App\Modules\Inspections\Inspections\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\User;
use App\Modules\Inspections\Inspections\InspectionService;
use App\Modules\Inspections\Inspections\Requests\StoreInspectionEvidenceRequest;
use App\Modules\Inspections\Inspections\Requests\StoreInspectionRequest;
use App\Modules\Inspections\Inspections\Requests\SubmitInspectionRequest;
use App\Modules\Inspections\Inspections\Requests\UpdateInspectionItemsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionController extends Controller
{
    private const OPS_ROLES = ['super_admin', 'operational_admin', 'supervisor'];

    public function __construct(private readonly InspectionService $inspectionService)
    {
    }

    public function index(Request $request): View
    {
        $actor = $request->user();
        $isOpsActor = in_array((string) $actor?->role, self::OPS_ROLES, true);

        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'inspection_date' => $request->input('inspection_date'),
            'inspection_officer_id' => $request->input('inspection_officer_id'),
            'schedule_type' => $request->input('schedule_type'),
        ];

        $inspections = $isOpsActor
            ? $this->inspectionService->paginateInspectionTasksForOps(actor: $actor, filters: $filters)
            : $this->inspectionService->paginateMyInspections(
                officer: $actor,
                filters: [
                    ...$filters,
                    'due_only' => true,
                ],
            );

        return view('modules.inspections.inspections.index', [
            'inspections' => $inspections,
            'filters' => $filters,
            'isOpsActor' => $isOpsActor,
            'statusOptions' => [
                Inspection::STATUS_DRAFT,
                Inspection::STATUS_IN_PROGRESS,
                Inspection::STATUS_SUBMITTED,
            ],
            'scheduleTypeOptions' => [
                Inspection::SCHEDULE_TYPE_NONE,
                Inspection::SCHEDULE_TYPE_DAILY,
                Inspection::SCHEDULE_TYPE_WEEKLY,
            ],
            'officerOptions' => $isOpsActor
                ? User::query()
                    ->whereIn('role', ['inspection_officer', 'engineer'])
                    ->orderBy('name')
                    ->get(['id', 'name', 'role'])
                : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless(in_array((string) $request->user()?->role, self::OPS_ROLES, true), 403);

        return view('modules.inspections.inspections.create', [
            'templateOptions' => InspectionTemplate::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'locationOptions' => AssetLocation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'officerOptions' => User::query()
                ->whereIn('role', ['inspection_officer', 'engineer'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
            'scheduleTypeOptions' => [
                Inspection::SCHEDULE_TYPE_NONE,
                Inspection::SCHEDULE_TYPE_DAILY,
                Inspection::SCHEDULE_TYPE_WEEKLY,
            ],
        ]);
    }

    public function store(StoreInspectionRequest $request): RedirectResponse
    {
        abort_unless(in_array((string) $request->user()?->role, self::OPS_ROLES, true), 403);

        $inspection = $this->inspectionService->createForOfficer($request->validated(), $request->user());

        return redirect()
            ->route('inspections.index')
            ->with('success', "Inspection task {$inspection->inspection_number} has been scheduled.");
    }

    public function show(Request $request, Inspection $inspection): View
    {
        $actor = $request->user();
        $isOpsActor = in_array((string) $actor?->role, self::OPS_ROLES, true);

        if (! $isOpsActor) {
            $this->inspectionService->ensureOwnedByOfficer($inspection, $actor);
        }

        return view('modules.inspections.inspections.show', [
            'inspection' => $inspection->load($this->inspectionService->inspectionRelations()),
            'resultStatusOptions' => ['pass', 'fail', 'na'],
            'canExecuteInspection' => ! $isOpsActor,
        ]);
    }

    public function updateItems(UpdateInspectionItemsRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->inspectionService->updateItems($inspection, $request->user(), $request->validated('items'));

        return back()->with('success', 'Inspection items have been updated.');
    }

    public function submit(SubmitInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->inspectionService->submit(
            inspection: $inspection,
            officer: $request->user(),
            finalResult: $request->validated('final_result'),
            summaryNotes: $request->validated('summary_notes'),
            supportingFiles: $request->file('supporting_files', []),
        );

        return back()->with('success', 'Inspection has been submitted.');
    }

    public function storeEvidence(StoreInspectionEvidenceRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->inspectionService->addEvidence(
            inspection: $inspection,
            officer: $request->user(),
            file: $request->file('file'),
            notes: $request->validated('notes'),
            inspectionItemId: $request->validated('inspection_item_id'),
        );

        return back()->with('success', 'Evidence has been uploaded.');
    }
}
