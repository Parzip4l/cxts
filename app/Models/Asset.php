<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'asset_category_id',
        'service_id',
        'department_owner_id',
        'vendor_id',
        'asset_location_id',
        'serial_number',
        'brand',
        'model',
        'install_date',
        'warranty_end_date',
        'criticality',
        'asset_status_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'install_date' => 'date',
        'warranty_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public const CRITICALITY_LOW = 'low';
    public const CRITICALITY_MEDIUM = 'medium';
    public const CRITICALITY_HIGH = 'high';
    public const CRITICALITY_CRITICAL = 'critical';

    public static function criticalityOptions(): array
    {
        return [
            self::CRITICALITY_LOW,
            self::CRITICALITY_MEDIUM,
            self::CRITICALITY_HIGH,
            self::CRITICALITY_CRITICAL,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function ownerDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_owner_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'asset_status_id');
    }
}
