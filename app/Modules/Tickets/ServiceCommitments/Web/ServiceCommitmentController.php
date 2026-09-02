<?php

namespace App\Modules\Tickets\ServiceCommitments\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\ServiceCommitment;
use App\Models\Vendor;
use App\Modules\Tickets\ServiceCommitments\Requests\ServiceCommitmentRequest;
use App\Modules\Tickets\ServiceCommitments\ServiceCommitmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCommitmentController extends Controller
{
    public function __construct(private readonly ServiceCommitmentService $serviceCommitmentService)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'commitment_type' => $request->input('commitment_type'),
            'status' => $request->input('status'),
            'service_id' => $request->input('service_id'),
        ];

        return view('modules.tickets.service-commitments.index', [
            'commitments' => $this->serviceCommitmentService->paginate($filters),
            'filters' => $filters,
            ...$this->formOptions(),
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.service-commitments.form', [
            'commitment' => new ServiceCommitment([
                'commitment_type' => ServiceCommitment::TYPE_OLA,
                'status' => ServiceCommitment::STATUS_ACTIVE,
            ]),
            ...$this->formOptions(),
            'action' => route('service-commitments.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Service Commitment',
        ]);
    }

    public function store(ServiceCommitmentRequest $request): RedirectResponse
    {
        $this->serviceCommitmentService->create($request->validated(), $request->user());

        return redirect()
            ->route('service-commitments.index')
            ->with('success', 'Service commitment has been created.');
    }

    public function edit(ServiceCommitment $serviceCommitment): View
    {
        return view('modules.tickets.service-commitments.form', [
            'commitment' => $serviceCommitment,
            ...$this->formOptions(),
            'action' => route('service-commitments.update', $serviceCommitment),
            'method' => 'PUT',
            'pageTitle' => 'Edit Service Commitment',
        ]);
    }

    public function update(ServiceCommitmentRequest $request, ServiceCommitment $serviceCommitment): RedirectResponse
    {
        $this->serviceCommitmentService->update($serviceCommitment, $request->validated(), $request->user());

        return redirect()
            ->route('service-commitments.index')
            ->with('success', 'Service commitment has been updated.');
    }

    public function destroy(ServiceCommitment $serviceCommitment): RedirectResponse
    {
        $this->serviceCommitmentService->delete($serviceCommitment);

        return redirect()
            ->route('service-commitments.index')
            ->with('success', 'Service commitment has been deleted.');
    }

    private function formOptions(): array
    {
        return [
            'typeOptions' => ServiceCommitment::typeOptions(),
            'statusOptions' => ServiceCommitment::statusOptions(),
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
