<?php

namespace App\Modules\Tickets\PublicAccess\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Tickets\PublicAccess\Requests\StorePublicTicketRequest;
use App\Modules\Tickets\PublicAccess\Requests\TrackPublicTicketRequest;
use App\Modules\Tickets\Tickets\TicketService;
use App\Notifications\PublicTicketSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

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
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->where('is_requestable', true)->orderBy('name')->get(['id', 'name', 'request_form_schema']),
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'service_id', 'asset_location_id', 'asset_category_id']),
            'locationOptions' => AssetLocation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'defaultPriorityId' => $this->resolveDefaultPriorityId($priorityOptions),
            'processTypeOptions' => Ticket::processTypeOptions(),
        ]);
    }

    public function track(): View
    {
        $ticketNumber = session('ticket_number');
        $requesterEmail = session('requester_email');
        $ticket = null;

        if (is_string($ticketNumber) && is_string($requesterEmail)) {
            $ticket = $this->findPublicTicket($ticketNumber, $requesterEmail);
        }

        return view('public.tickets.track', [
            'ticket' => $ticket,
            'ticketNumber' => $ticketNumber,
            'requesterEmail' => $requesterEmail,
        ]);
    }

    public function lookup(TrackPublicTicketRequest $request): View|RedirectResponse
    {
        $data = $request->validated();
        $ticket = $this->findPublicTicket($data['ticket_number'], $data['requester_email']);

        if ($ticket === null) {
            return back()
                ->withErrors(['ticket_number' => 'Ticket tidak ditemukan untuk kombinasi nomor ticket dan email pelapor tersebut.'])
                ->withInput();
        }

        return view('public.tickets.track', [
            'ticket' => $ticket,
            'ticketNumber' => $data['ticket_number'],
            'requesterEmail' => $data['requester_email'],
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
            'process_type' => Ticket::normalizeProcessType($data['process_type'] ?? $data['ticket_type'] ?? null),
            'ticket_type' => $data['ticket_type'] ?? null,
            'requester_id' => $requester->id,
            'requester_department_id' => $data['requester_department_id'],
            'ticket_category_id' => $data['ticket_category_id'],
            'ticket_subcategory_id' => $data['ticket_subcategory_id'] ?? null,
            'ticket_detail_subcategory_id' => $data['ticket_detail_subcategory_id'] ?? null,
            'ticket_priority_id' => $data['ticket_priority_id'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'request_form_payload' => $data['request_form_payload'] ?? null,
            'asset_id' => $data['asset_id'] ?? null,
            'asset_location_id' => $data['asset_location_id'] ?? null,
            'source' => 'public_web',
            'impact' => $data['impact'] ?? 'medium',
            'urgency' => $data['urgency'] ?? 'medium',
            'attachments' => $data['attachments'] ?? [],
        ], $requester);

        try {
            $requester->notify(new PublicTicketSubmittedNotification($ticket));
        } catch (Throwable $exception) {
            Log::warning('Failed to send public ticket notification.', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'requester_id' => $requester->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('public.tickets.track')
            ->with('success', "Ticket berhasil dibuat dengan nomor {$ticket->ticket_number}. Simpan nomor ini untuk tracking status.")
            ->with('ticket_number', $ticket->ticket_number)
            ->with('requester_email', $requester->email);
    }

    private function findPublicTicket(string $ticketNumber, string $requesterEmail): ?Ticket
    {
        return Ticket::query()
            ->with([
                'requester:id,name,email',
                'requesterDepartment:id,name',
                'category:id,name',
                'subcategory:id,name',
                'detailSubcategory:id,name',
                'priority:id,name',
                'status:id,name,code',
                'service:id,name',
                'asset:id,name',
                'assetLocation:id,name',
                'assignedEngineer:id,name',
                'activities' => fn ($query) => $query
                    ->whereIn('activity_type', $this->publicActivityTypes())
                    ->oldest(),
                'activities.newStatus:id,name,code',
            ])
            ->where('ticket_number', $ticketNumber)
            ->whereHas('requester', fn ($query) => $query->whereRaw('LOWER(email) = ?', [Str::lower($requesterEmail)]))
            ->first();
    }

    /**
     * Public tracking intentionally exposes only status-level activities.
     *
     * @return array<int, string>
     */
    private function publicActivityTypes(): array
    {
        return [
            'ticket_created',
            'ticket_approved',
            'ticket_rejected',
            'ticket_ready_for_assignment',
            'ticket_assigned',
            'work_started',
            'work_paused',
            'work_resumed',
            'ticket_pending_customer',
            'work_completed',
            'ticket_closed',
            'ticket_reopened',
            'ticket_cancelled',
        ];
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
