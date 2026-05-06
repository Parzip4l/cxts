<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEngineerAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'engineer_id',
        'assigned_by_id',
        'team_name',
        'score_share',
        'assigned_at',
    ];

    protected $casts = [
        'score_share' => 'decimal:4',
        'assigned_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}
