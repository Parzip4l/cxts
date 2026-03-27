<?php

namespace App\Modules\Engineering\Web;

use App\Http\Controllers\Controller;
use App\Models\EngineerSchedule;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EngineeringController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasAnyPermission(['dashboard.view_ops', 'workforce.manage', 'engineer_task.view_assigned']),
            403
        );

        $today = Carbon::today();
        $search = trim((string) $request->string('search'));
        $availabilityFilter = strtolower(trim((string) $request->string('availability')));
        $perPage = 9;

        $engineers = User::query()
            ->where('role', 'engineer')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone_number', 'like', '%' . $search . '%')
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->with([
                'department:id,name',
                'engineerSkills:id,name',
                'engineerSchedules' => fn ($query) => $query
                    ->whereDate('work_date', $today)
                    ->with('shift:id,name,start_time,end_time,is_overnight')
                    ->latest('id'),
            ])
            ->orderBy('name')
            ->get();

        $engineerIds = $engineers->pluck('id');

        $ticketLoad = Ticket::query()
            ->selectRaw('assigned_engineer_id, COUNT(*) as active_ticket_count')
            ->selectRaw('SUM(CASE WHEN ticket_statuses.is_in_progress IS TRUE THEN 1 ELSE 0 END) as in_progress_ticket_count')
            ->join('ticket_statuses', 'ticket_statuses.id', '=', 'tickets.ticket_status_id')
            ->whereNotNull('assigned_engineer_id')
            ->when($engineerIds->isNotEmpty(), fn ($query) => $query->whereIn('assigned_engineer_id', $engineerIds))
            ->when($engineerIds->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
            ->where(function ($query): void {
                $query->whereRaw('ticket_statuses.is_open IS TRUE')
                    ->orWhereRaw('ticket_statuses.is_in_progress IS TRUE');
            })
            ->groupBy('assigned_engineer_id')
            ->get()
            ->keyBy('assigned_engineer_id');

        $cards = $engineers->map(function (User $engineer) use ($ticketLoad) {
            $todaySchedule = $engineer->engineerSchedules->first();
            $load = $ticketLoad->get($engineer->id);
            $activeTicketCount = (int) ($load->active_ticket_count ?? 0);
            $inProgressTicketCount = (int) ($load->in_progress_ticket_count ?? 0);

            [$availabilityLabel, $availabilityClass] = $this->resolveAvailability($todaySchedule?->status, $activeTicketCount);

            return [
                'engineer' => $engineer,
                'availability_label' => $availabilityLabel,
                'availability_class' => $availabilityClass,
                'schedule_status_label' => $this->scheduleStatusLabel($todaySchedule?->status),
                'schedule_status_class' => $this->scheduleStatusClass($todaySchedule?->status),
                'shift_label' => $this->shiftLabel($todaySchedule),
                'schedule_notes' => $todaySchedule?->notes,
                'avatar_initials' => $this->avatarInitials($engineer->name),
                'avatar_class' => $this->avatarClass($engineer->name),
                'profile_photo_url' => $engineer->profilePhotoUrl(),
                'active_ticket_count' => $activeTicketCount,
                'in_progress_ticket_count' => $inProgressTicketCount,
                'workload_percent' => $this->workloadPercent($activeTicketCount, $inProgressTicketCount),
                'workload_label' => $this->workloadLabel($activeTicketCount, $inProgressTicketCount),
                'whatsapp_url' => $engineer->whatsappUrl(),
                'tel_url' => $engineer->telUrl(),
                'skill_names' => $engineer->engineerSkills->pluck('name')->take(4)->values(),
            ];
        });

        $cards = $this->filterByAvailability($cards, $availabilityFilter);

        $summary = [
            'total_engineers' => $cards->count(),
            'available' => $cards->where('availability_label', 'Available')->count(),
            'busy' => $cards->where('availability_label', 'Busy')->count(),
            'off' => $cards->whereIn('availability_label', ['Off Duty', 'On Leave', 'Sick'])->count(),
            'unscheduled' => $cards->where('availability_label', 'Unscheduled')->count(),
        ];

        $departmentSummary = $cards
            ->groupBy(fn (array $card) => $card['engineer']->department?->name ?? 'No Department')
            ->map(function (Collection $items, string $department): array {
                return [
                    'department' => $department,
                    'engineer_count' => $items->count(),
                    'available_count' => $items->where('availability_label', 'Available')->count(),
                    'busy_count' => $items->where('availability_label', 'Busy')->count(),
                    'active_ticket_count' => $items->sum('active_ticket_count'),
                    'in_progress_ticket_count' => $items->sum('in_progress_ticket_count'),
                ];
            })
            ->sortByDesc('active_ticket_count')
            ->values();

        $cards = $this->paginateCards($cards, $perPage, (int) $request->integer('page', 1), $request);

        return view('modules.engineering.index', [
            'today' => $today,
            'search' => $search,
            'availabilityFilter' => $availabilityFilter,
            'summary' => $summary,
            'departmentSummary' => $departmentSummary,
            'cards' => $cards,
        ]);
    }

    private function filterByAvailability(Collection $cards, string $availabilityFilter): Collection
    {
        return match ($availabilityFilter) {
            'available' => $cards->where('availability_label', 'Available')->values(),
            'busy' => $cards->where('availability_label', 'Busy')->values(),
            'off' => $cards->whereIn('availability_label', ['Off Duty', 'On Leave', 'Sick'])->values(),
            'unscheduled' => $cards->where('availability_label', 'Unscheduled')->values(),
            default => $cards->values(),
        };
    }

    private function resolveAvailability(?string $scheduleStatus, int $activeTicketCount): array
    {
        return match ($scheduleStatus) {
            EngineerSchedule::STATUS_OFF => ['Off Duty', 'dark'],
            EngineerSchedule::STATUS_LEAVE => ['On Leave', 'warning'],
            EngineerSchedule::STATUS_SICK => ['Sick', 'danger'],
            null => ['Unscheduled', 'secondary'],
            default => $activeTicketCount > 0
                ? ['Busy', 'danger']
                : ['Available', 'success'],
        };
    }

    private function scheduleStatusLabel(?string $scheduleStatus): string
    {
        return match ($scheduleStatus) {
            EngineerSchedule::STATUS_ASSIGNED => 'Scheduled',
            EngineerSchedule::STATUS_OFF => 'Off Day',
            EngineerSchedule::STATUS_LEAVE => 'Leave',
            EngineerSchedule::STATUS_SICK => 'Sick Leave',
            default => 'No Schedule',
        };
    }

    private function scheduleStatusClass(?string $scheduleStatus): string
    {
        return match ($scheduleStatus) {
            EngineerSchedule::STATUS_ASSIGNED => 'primary',
            EngineerSchedule::STATUS_OFF => 'dark',
            EngineerSchedule::STATUS_LEAVE => 'warning',
            EngineerSchedule::STATUS_SICK => 'danger',
            default => 'secondary',
        };
    }

    private function shiftLabel(?EngineerSchedule $schedule): string
    {
        if ($schedule === null || $schedule->shift === null) {
            return 'No shift assigned for today';
        }

        $start = substr((string) $schedule->shift->start_time, 0, 5);
        $end = substr((string) $schedule->shift->end_time, 0, 5);

        return trim($schedule->shift->name . ' · ' . $start . ' - ' . $end);
    }

    private function avatarInitials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)));

        return $parts->isNotEmpty() ? $parts->implode('') : 'NA';
    }

    private function avatarClass(string $name): string
    {
        $classes = ['primary', 'success', 'info', 'warning', 'danger', 'dark'];
        $index = abs(crc32($name)) % count($classes);

        return $classes[$index];
    }

    private function paginateCards(Collection $cards, int $perPage, int $page, Request $request): LengthAwarePaginator
    {
        $page = max($page, 1);
        $items = $cards->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $cards->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function workloadPercent(int $activeTicketCount, int $inProgressTicketCount): int
    {
        $score = ($activeTicketCount * 20) + ($inProgressTicketCount * 10);

        return max(0, min($score, 100));
    }

    private function workloadLabel(int $activeTicketCount, int $inProgressTicketCount): string
    {
        $percent = $this->workloadPercent($activeTicketCount, $inProgressTicketCount);

        return match (true) {
            $percent >= 80 => 'High Load',
            $percent >= 40 => 'Moderate Load',
            $percent > 0 => 'Light Load',
            default => 'Idle',
        };
    }
}
