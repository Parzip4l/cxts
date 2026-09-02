<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_configuration_item',
        'ci_type',
        'ci_lifecycle_state',
        'ci_governance_note',
        'asset_status_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'install_date' => 'date',
        'warranty_end_date' => 'date',
        'is_configuration_item' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const CRITICALITY_LOW = 'low';
    public const CRITICALITY_MEDIUM = 'medium';
    public const CRITICALITY_HIGH = 'high';
    public const CRITICALITY_CRITICAL = 'critical';

    public const CI_TYPE_APPLICATION = 'application';
    public const CI_TYPE_INFRASTRUCTURE = 'infrastructure';
    public const CI_TYPE_NETWORK = 'network';
    public const CI_TYPE_SERVICE_COMPONENT = 'service_component';
    public const CI_TYPE_ENDPOINT = 'endpoint';

    public const CI_STATE_PLANNED = 'planned';
    public const CI_STATE_ACTIVE = 'active';
    public const CI_STATE_MAINTENANCE = 'maintenance';
    public const CI_STATE_RETIRED = 'retired';

    public static function criticalityOptions(): array
    {
        return [
            self::CRITICALITY_LOW,
            self::CRITICALITY_MEDIUM,
            self::CRITICALITY_HIGH,
            self::CRITICALITY_CRITICAL,
        ];
    }

    public static function ciTypeOptions(): array
    {
        return [
            self::CI_TYPE_APPLICATION => 'Application',
            self::CI_TYPE_INFRASTRUCTURE => 'Infrastructure',
            self::CI_TYPE_NETWORK => 'Network',
            self::CI_TYPE_SERVICE_COMPONENT => 'Service Component',
            self::CI_TYPE_ENDPOINT => 'Endpoint',
        ];
    }

    public static function ciLifecycleOptions(): array
    {
        return [
            self::CI_STATE_PLANNED => 'Planned',
            self::CI_STATE_ACTIVE => 'Active',
            self::CI_STATE_MAINTENANCE => 'Maintenance',
            self::CI_STATE_RETIRED => 'Retired',
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

    public function relationships(): HasMany
    {
        return $this->hasMany(AssetRelationship::class);
    }

    public function dependsOnRelationships(): HasMany
    {
        return $this->relationships()->where('relationship_type', AssetRelationship::TYPE_DEPENDS_ON);
    }

    public function supportsRelationships(): HasMany
    {
        return $this->relationships()->where('relationship_type', AssetRelationship::TYPE_SUPPORTS);
    }

    public function impactedByRelationships(): HasMany
    {
        return $this->hasMany(AssetRelationship::class, 'related_asset_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
