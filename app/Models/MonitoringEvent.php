<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringEvent extends Model
{
    use HasFactory;

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    public const STATUS_OPEN = 'open';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'event_number',
        'source',
        'severity',
        'service_id',
        'asset_id',
        'message',
        'details',
        'occurred_at',
        'status',
        'deduplication_key',
        'duplicate_count',
        'last_seen_at',
        'converted_ticket_id',
        'converted_at',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'converted_at' => 'datetime',
        'duplicate_count' => 'integer',
    ];

    public static function severityOptions(): array
    {
        return [
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_IGNORED => 'Ignored',
        ];
    }

    public function severityLabel(): string
    {
        return self::severityOptions()[$this->severity] ?? str($this->severity)->headline()->toString();
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? str($this->status)->headline()->toString();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function convertedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'converted_ticket_id');
    }
}
