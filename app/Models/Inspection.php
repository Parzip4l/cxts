<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inspection extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const FINAL_RESULT_NORMAL = 'normal';
    public const FINAL_RESULT_ABNORMAL = 'abnormal';
    public const SCHEDULE_TYPE_NONE = 'none';
    public const SCHEDULE_TYPE_DAILY = 'daily';
    public const SCHEDULE_TYPE_WEEKLY = 'weekly';

    protected $fillable = [
        'inspection_number',
        'inspection_template_id',
        'asset_id',
        'asset_location_id',
        'inspection_officer_id',
        'scheduled_by_id',
        'inspection_date',
        'schedule_next_date',
        'status',
        'schedule_type',
        'schedule_interval',
        'schedule_weekdays',
        'final_result',
        'started_at',
        'submitted_at',
        'summary_notes',
        'parent_inspection_id',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'schedule_next_date' => 'date',
        'schedule_weekdays' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assetLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspection_officer_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_id');
    }

    public function parentInspection(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_inspection_id');
    }

    public function nextInspections(): HasMany
    {
        return $this->hasMany(self::class, 'parent_inspection_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class)->orderBy('sequence');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(InspectionEvidence::class)->latest();
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'inspection_id');
    }
}
