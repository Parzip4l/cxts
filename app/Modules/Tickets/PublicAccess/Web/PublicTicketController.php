<?php

namespace App\Modules\Tickets\PublicAccess\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Tickets\PublicAccess\Requests\StorePublicTicketRequest;
use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicTicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function create(): View
    {
        $priorityOptions = TicketPriority::query()->where('is_active', true)->orderBy('level')->get(['id', 'code', 'name']);

        return view('public.tickets.create', [
            'departmentOptions' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categoryOptions' => TicketCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subcategoryOptions' => TicketSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'detailSubcategoryOptions' => TicketDetailSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_subcategory_id']),
            'priorityOptions' => $priorityOptions,
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'service_id', 'asset_location_id', 'asset_category_id']),
            'locationOptions' => AssetLocation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'defaultPriorityId' => $this->resolveDefaultPriorityId($priorityOptions),
        ]);
    }

    public function store(StorePublicTicketRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $requester = $this->findOrCreatePublicRequester(
            name: $data['requester_name'],
            email: $data['requester_email'],
            departmentId: (int) $data['requester_department_id'],
        );

        $ticket = $this->ticketService->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'ticket_type' => $data['ticket_type'] ?? null,
            'requester_id' => $requester->id,
            'requester_department_id' => $data['requester_department_id'],
            'ticket_category_id' => $data['ticket_category_id'],
            'ticket_subcategory_id' => $data['ticket_subcategory_id'] ?? null,
            'ticket_detail_subcategory_id' => $data['ticket_detail_subcategory_id'] ?? null,
            'ticket_priority_id' => $data['ticket_priority_id'] ?? $this->resolveDefaultPriorityId(),
            'service_id' => $data['service_id'] ?? null,
            'asset_id' => $data['asset_id'] ?? null,
            'asset_location_id' => $data['asset_location_id'] ?? null,
            'source' => 'public_web',
            'impact' => $data['impact'] ?? 'medium',
            'urgency' => $data['urgency'] ?? 'medium',
            'attachments' => $data['attachments'] ?? [],
        ], $requester);

        return redirect()
            ->route('public.tickets.create')
            ->with('success', "Ticket berhasil dibuat dengan nomor {$ticket->ticket_number}.");
    }

    private function findOrCreatePublicRequester(string $name, string $email, int $departmentId): User
    {
        $user = User::query()->where('email', $email)->first();
        if ($user !== null) {
            if ($user->department_id === null) {
                $user->department_id = $departmentId;
            }

            if ($user->name === '' || $user->name === null) {
                $user->name = $name;
            }

            $user->save();

            return $user;
        }

        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(24)),
            'role' => 'requester',
            'department_id' => $departmentId,
            'email_verified_at' => now(),
        ]);
    }

    private function resolveDefaultPriorityId($priorityOptions = null): ?int
    {
        $priorityOptions ??= TicketPriority::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->get(['id', 'code', 'name']);

        $defaultPriority = $priorityOptions->firstWhere('code', 'P3')
            ?? $priorityOptions->first(fn ($priority) => strcasecmp((string) $priority->name, 'Medium') === 0)
            ?? $priorityOptions->first();

        return $defaultPriority?->id;
    }
}
