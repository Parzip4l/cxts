<?php

namespace App\Modules\Tickets\Monitoring\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitoringEventResource;
use App\Models\MonitoringEvent;
use App\Modules\Tickets\Monitoring\MonitoringEventService;
use App\Modules\Tickets\Monitoring\Requests\MonitoringEventRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringEventController extends Controller
{
    public function __construct(private readonly MonitoringEventService $monitoringEventService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'severity' => $request->input('severity'),
            'status' => $request->input('status'),
            'source' => $request->input('source'),
        ];

        return MonitoringEventResource::collection(
            $this->monitoringEventService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(MonitoringEventRequest $request): JsonResponse
    {
        return (new MonitoringEventResource($this->monitoringEventService->create($request->validated(), $request->user())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MonitoringEvent $monitoringEvent): MonitoringEventResource
    {
        return new MonitoringEventResource($monitoringEvent->load(['service:id,name', 'asset:id,name', 'convertedTicket', 'convertedTicket.status:id,name,code']));
    }

    public function convert(Request $request, MonitoringEvent $monitoringEvent): MonitoringEventResource
    {
        return new MonitoringEventResource($this->monitoringEventService->convertToIncident($monitoringEvent, $request->user()));
    }

    public function ignore(Request $request, MonitoringEvent $monitoringEvent): MonitoringEventResource
    {
        return new MonitoringEventResource($this->monitoringEventService->ignore($monitoringEvent, $request->user()));
    }
}
