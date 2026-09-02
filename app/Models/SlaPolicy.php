<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'response_time_minutes',
        'resolution_time_minutes',
        'working_hours_id',
        'escalate_on_warning',
        'escalate_on_breach',
        'escalation_role_code',
        'escalation_note',
        'is_active',
    ];

    protected $casts = [
        'escalate_on_warning' => 'boolean',
        'escalate_on_breach' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(SlaPolicyAssignment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function slaEvents(): HasMany
    {
        return $this->hasMany(SlaEvent::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SlaPolicyAuditLog::class);
    }
}
