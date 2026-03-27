<?php

namespace App\Modules\Tickets\TicketStatuses\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketStatusResource;
use App\Models\TicketStatus;
use App\Modules\Tickets\TicketStatuses\Requests\StoreTicketStatusRequest;
use App\Modules\Tickets\TicketStatuses\Requests\UpdateTicketStatusRequest;
use App\Modules\Tickets\TicketStatuses\TicketStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketStatusController extends Controller
{
    public function __construct(private readonly TicketStatusService $ticketStatusService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketStatuses = $this->ticketStatusService->paginate($filters, (int) $request->input('per_page', 15));

        return TicketStatusResource::collection($ticketStatuses);
    }

    public function store(StoreTicketStatusRequest $request): TicketStatusResource
    {
        return new TicketStatusResource($this->ticketStatusService->create($request->validated()));
    }

    public function show(TicketStatus $ticketStatus): TicketStatusResource
    {
        return new TicketStatusResource($ticketStatus);
    }

    public function update(UpdateTicketStatusRequest $request, TicketStatus $ticketStatus): TicketStatusResource
    {
        return new TicketStatusResource($this->ticketStatusService->update($ticketStatus, $request->validated()));
    }

    public function destroy(TicketStatus $ticketStatus): JsonResponse
    {
        $this->ticketStatusService->delete($ticketStatus);

        return response()->json(['message' => 'Ticket status deleted.']);
    }
}
