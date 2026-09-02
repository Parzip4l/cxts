<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCommitment extends Model
{
    use HasFactory;

    public const TYPE_OLA = 'ola';
    public const TYPE_UNDERPINNING_CONTRACT = 'underpinning_contract';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_REVIEW = 'review';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'commitment_number',
        'name',
        'commitment_type',
        'service_id',
        'provider_department_id',
        'vendor_id',
        'response_target_minutes',
        'resolution_target_minutes',
        'availability_target_percent',
        'escalation_contact',
        'review_frequency',
        'effective_from',
        'effective_until',
        'status',
        'notes',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'response_target_minutes' => 'integer',
        'resolution_target_minutes' => 'integer',
        'availability_target_percent' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_OLA => 'OLA Internal',
            self::TYPE_UNDERPINNING_CONTRACT => 'Underpinning Contract',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_REVIEW => 'Under Review',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->commitment_type] ?? str($this->commitment_type)->headline()->toString();
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? str($this->status)->headline()->toString();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function providerDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'provider_department_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
