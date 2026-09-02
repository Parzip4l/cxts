<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaEvent extends Model
{
    use HasFactory;

    public const TYPE_STATE_CHANGED = 'state_changed';
    public const TYPE_WARNING = 'warning';
    public const TYPE_BREACH = 'breach';
    public const TYPE_ESCALATION = 'escalation_triggered';

    protected $fillable = [
        'ticket_id',
        'sla_policy_id',
        'event_type',
        'target',
        'event_at',
        'due_at',
        'threshold_percentage',
        'old_sla_status',
        'new_sla_status',
        'escalation_role_code',
        'actor_user_id',
        'metadata',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'due_at' => 'datetime',
        'threshold_percentage' => 'integer',
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
