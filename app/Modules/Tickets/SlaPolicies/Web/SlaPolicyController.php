<?php

namespace App\Modules\Tickets\SlaPolicies\Web;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use App\Modules\Tickets\SlaPolicies\Requests\StoreSlaPolicyRequest;
use App\Modules\Tickets\SlaPolicies\Requests\UpdateSlaPolicyRequest;
use App\Modules\Tickets\SlaPolicies\SlaPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPolicyController extends Controller
{
    public function __construct(private readonly SlaPolicyService $slaPolicyService)
    {
        $this->authorizeResource(SlaPolicy::class, 'sla_policy');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return view('modules.tickets.sla-policies.index', [
            'slaPolicies' => $this->slaPolicyService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.sla-policies.form', [
            'slaPolicy' => new SlaPolicy(),
            'action' => route('master-data.sla-policies.store'),
            'method' => 'POST',
            'pageTitle' => 'Create SLA Policy',
        ]);
    }

    public function store(StoreSlaPolicyRequest $request): RedirectResponse
    {
        $this->slaPolicyService->create($request->validated());

        return redirect()
            ->route('master-data.sla-policies.index')
            ->with('success', 'SLA policy has been created.');
    }

    public function edit(SlaPolicy $slaPolicy): View
    {
        return view('modules.tickets.sla-policies.form', [
            'slaPolicy' => $slaPolicy,
            'action' => route('master-data.sla-policies.update', $slaPolicy),
            'method' => 'PUT',
            'pageTitle' => 'Edit SLA Policy',
        ]);
    }

    public function update(UpdateSlaPolicyRequest $request, SlaPolicy $slaPolicy): RedirectResponse
    {
        $this->slaPolicyService->update($slaPolicy, $request->validated());

        return redirect()
            ->route('master-data.sla-policies.index')
            ->with('success', 'SLA policy has been updated.');
    }

    public function destroy(SlaPolicy $slaPolicy): RedirectResponse
    {
        $this->slaPolicyService->delete($slaPolicy);

        return redirect()
            ->route('master-data.sla-policies.index')
            ->with('success', 'SLA policy has been deleted.');
    }
}
