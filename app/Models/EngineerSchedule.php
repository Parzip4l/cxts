<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerSchedule extends Model
{
    use HasFactory;

    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_OFF = 'off';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_SICK = 'sick';

    protected $fillable = [
        'user_id',
        'shift_id',
        'work_date',
        'status',
        'notes',
        'assigned_by_id',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}
