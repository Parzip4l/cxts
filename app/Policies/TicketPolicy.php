<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'ticket.view_all',
            'ticket.view_department',
            'ticket.view_assigned',
            'ticket.view_own',
        ]);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('ticket.view_all')) {
            return true;
        }

        if ($ticket->canBeApprovedBy($user)) {
            return true;
        }

        if ($user->hasPermission('ticket.view_department')
            && $user->department_id !== null
            && (int) $ticket->requester_department_id === (int) $user->department_id) {
            return true;
        }

        if ($user->hasPermission('ticket.view_assigned')
            && (int) $ticket->assigned_engineer_id === (int) $user->id) {
            return true;
        }

        if ($user->hasPermission('ticket.view_own')
            && (int) $ticket->requester_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'ticket.create_any',
            'ticket.create_self',
        ]);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('ticket.assign_all')) {
            return true;
        }

        return $user->hasPermission('ticket.assign_department')
            && $user->department_id !== null
            && (int) $ticket->requester_department_id === (int) $user->department_id;
    }

    public function approve(User $user, Ticket $ticket): bool
    {
        if (! $ticket->canBeApprovedBy($user)) {
            return false;
        }

        if ($user->hasPermission('ticket.approve_all')) {
            return true;
        }

        return $user->hasPermission('ticket.approve_department');
    }

    public function reject(User $user, Ticket $ticket): bool
    {
        return $this->approve($user, $ticket);
    }

    public function markReady(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('ticket.mark_ready_all')) {
            return true;
        }

        if (! $user->hasPermission('ticket.mark_ready_department')) {
            return false;
        }

        if ($ticket->canBeApprovedBy($user)) {
            return true;
        }

        return $user->department_id !== null
            && (int) $ticket->requester_department_id === (int) $user->department_id;
    }

    public function work(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('engineer_task.transition_assigned')
            && (int) $ticket->assigned_engineer_id === (int) $user->id;
    }

    public function addWorklog(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('engineer_task.worklog_assigned')
            && (int) $ticket->assigned_engineer_id === (int) $user->id;
    }
}
