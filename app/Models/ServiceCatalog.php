<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCatalog extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'code',
        'name',
        'service_category',
        'description',
        'ownership_model',
        'department_owner_id',
        'vendor_id',
        'service_manager_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const OWNERSHIP_INTERNAL = 'internal';
    public const OWNERSHIP_VENDOR = 'vendor';
    public const OWNERSHIP_HYBRID = 'hybrid';

    public static function ownershipOptions(): array
    {
        return [
            self::OWNERSHIP_INTERNAL,
            self::OWNERSHIP_VENDOR,
            self::OWNERSHIP_HYBRID,
        ];
    }

    public function ownerDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_owner_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_manager_user_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'service_id');
    }

    public function engineerSkills(): BelongsToMany
    {
        return $this->belongsToMany(EngineerSkill::class, 'engineer_skill_service', 'service_id', 'engineer_skill_id')
            ->withTimestamps();
    }
}
