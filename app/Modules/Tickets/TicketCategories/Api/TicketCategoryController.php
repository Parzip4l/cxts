<?php

namespace App\Modules\Tickets\TicketCategories\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketCategoryResource;
use App\Models\TicketCategory;
use App\Modules\Tickets\TicketCategories\Requests\StoreTicketCategoryRequest;
use App\Modules\Tickets\TicketCategories\Requests\UpdateTicketCategoryRequest;
use App\Modules\Tickets\TicketCategories\TicketCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function __construct(private readonly TicketCategoryService $ticketCategoryService)
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

        $ticketCategories = $this->ticketCategoryService->paginate($filters, (int) $request->input('per_page', 15));

        return TicketCategoryResource::collection($ticketCategories);
    }

    public function store(StoreTicketCategoryRequest $request): TicketCategoryResource
    {
        return new TicketCategoryResource($this->ticketCategoryService->create($request->validated()));
    }

    public function show(TicketCategory $ticketCategory): TicketCategoryResource
    {
        return new TicketCategoryResource($ticketCategory->load('approver:id,name'));
    }

    public function update(UpdateTicketCategoryRequest $request, TicketCategory $ticketCategory): TicketCategoryResource
    {
        return new TicketCategoryResource($this->ticketCategoryService->update($ticketCategory, $request->validated()));
    }

    public function destroy(TicketCategory $ticketCategory): JsonResponse
    {
        $this->ticketCategoryService->delete($ticketCategory);

        return response()->json(['message' => 'Ticket category deleted.']);
    }
}
