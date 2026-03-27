<?php

namespace App\Modules\AuditTrail\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'super_admin', 403);

        $filters = [
            'search' => trim((string) $request->input('search')),
            'module' => $request->input('module'),
            'actor_user_id' => $request->input('actor_user_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($filters['search'], function ($query, $search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('action', 'like', '%' . $search . '%')
                        ->orWhere('path', 'like', '%' . $search . '%')
                        ->orWhere('subject_type', 'like', '%' . $search . '%')
                        ->orWhere('subject_id', 'like', '%' . $search . '%')
                        ->orWhereHas('actor', fn ($actorQuery) => $actorQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%'));
                });
            })
            ->when($filters['module'], fn ($query, $module) => $query->where('module', $module))
            ->when($filters['actor_user_id'], fn ($query, $actorId) => $query->where('actor_user_id', $actorId))
            ->when($filters['date_from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('modules.audit-trail.index', [
            'logs' => $logs,
            'filters' => $filters,
            'moduleOptions' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actorOptions' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
