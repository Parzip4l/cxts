<?php

namespace App\Modules\Inspections\InspectionTemplates\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\InspectionTemplate;
use App\Modules\Inspections\InspectionTemplates\InspectionTemplateService;
use App\Modules\Inspections\InspectionTemplates\Requests\StoreInspectionTemplateRequest;
use App\Modules\Inspections\InspectionTemplates\Requests\UpdateInspectionTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionTemplateController extends Controller
{
    public function __construct(private readonly InspectionTemplateService $inspectionTemplateService)
    {
        $this->authorizeResource(InspectionTemplate::class, 'inspection_template');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'asset_category_id' => $request->input('asset_category_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $inspectionTemplates = $this->inspectionTemplateService->paginate($filters);

        return view('modules.inspections.templates.index', [
            'inspectionTemplates' => $inspectionTemplates,
            'filters' => $filters,
            'assetCategoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('modules.inspections.templates.form', [
            'inspectionTemplate' => new InspectionTemplate(),
            'assetCategoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.inspection-templates.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Inspection Template',
        ]);
    }

    public function store(StoreInspectionTemplateRequest $request): RedirectResponse
    {
        $template = $this->inspectionTemplateService->create($request->validated(), $request->user());

        return redirect()
            ->route('master-data.inspection-templates.edit', $template)
            ->with('success', 'Inspection template has been created.');
    }

    public function edit(InspectionTemplate $inspectionTemplate): View
    {
        $inspectionTemplate->load(['items']);

        return view('modules.inspections.templates.form', [
            'inspectionTemplate' => $inspectionTemplate,
            'assetCategoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.inspection-templates.update', $inspectionTemplate),
            'method' => 'PUT',
            'pageTitle' => 'Edit Inspection Template',
        ]);
    }

    public function update(UpdateInspectionTemplateRequest $request, InspectionTemplate $inspectionTemplate): RedirectResponse
    {
        $this->inspectionTemplateService->update($inspectionTemplate, $request->validated(), $request->user());

        return redirect()
            ->route('master-data.inspection-templates.index')
            ->with('success', 'Inspection template has been updated.');
    }

    public function destroy(InspectionTemplate $inspectionTemplate): RedirectResponse
    {
        $this->inspectionTemplateService->delete($inspectionTemplate);

        return redirect()
            ->route('master-data.inspection-templates.index')
            ->with('success', 'Inspection template has been deleted.');
    }
}
