<?php

namespace App\Modules\Tickets\Monitoring\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MonitoringEvent;
use App\Models\ServiceCatalog;
use App\Modules\Tickets\Monitoring\MonitoringEventService;
use App\Modules\Tickets\Monitoring\Requests\MonitoringEventRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringEventController extends Controller
{
    public function __construct(private readonly MonitoringEventService $monitoringEventService)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'severity' => $request->input('severity'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
        ];

        return view('modules.tickets.monitoring.index', [
            'events' => $this->monitoringEventService->paginate($filters),
            'filters' => $filters,
            'severityOptions' => MonitoringEvent::severityOptions(),
            'statusOptions' => MonitoringEvent::statusOptions(),
            'sourceOptions' => MonitoringEvent::query()->distinct()->orderBy('source')->pluck('source'),
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.monitoring.form', [
            'event' => new MonitoringEvent([
                'severity' => MonitoringEvent::SEVERITY_MEDIUM,
                'occurred_at' => now(),
            ]),
            ...$this->formOptions(),
            'action' => route('monitoring-events.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Monitoring Event',
        ]);
    }

    public function store(MonitoringEventRequest $request): RedirectResponse
    {
        $event = $this->monitoringEventService->create($request->validated(), $request->user());

        return redirect()
            ->route('monitoring-events.show', $event)
            ->with('success', 'Monitoring event has been recorded.');
    }

    public function show(MonitoringEvent $monitoringEvent): View
    {
        return view('modules.tickets.monitoring.show', [
            'event' => $monitoringEvent->load(['service:id,name', 'asset:id,name', 'convertedTicket', 'convertedTicket.status:id,name,code']),
            'severityOptions' => MonitoringEvent::severityOptions(),
            'statusOptions' => MonitoringEvent::statusOptions(),
            'mappedPriority' => $this->monitoringEventService->severityToImpactUrgency($monitoringEvent->severity),
        ]);
    }

    public function convert(Request $request, MonitoringEvent $monitoringEvent): RedirectResponse
    {
        $event = $this->monitoringEventService->convertToIncident($monitoringEvent, $request->user());

        return redirect()
            ->route('monitoring-events.show', $event)
            ->with('success', 'Monitoring event has been converted to incident.');
    }

    public function ignore(Request $request, MonitoringEvent $monitoringEvent): RedirectResponse
    {
        $event = $this->monitoringEventService->ignore($monitoringEvent, $request->user());

        return redirect()
            ->route('monitoring-events.show', $event)
            ->with('success', 'Monitoring event has been ignored.');
    }

    private function formOptions(): array
    {
        return [
            'severityOptions' => MonitoringEvent::severityOptions(),
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
