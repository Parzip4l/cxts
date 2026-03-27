<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'parent_department_id',
        'head_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id');
    }

    public function childDepartments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_department_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function ownedServices(): HasMany
    {
        return $this->hasMany(ServiceCatalog::class, 'department_owner_id');
    }

    public function assetLocations(): HasMany
    {
        return $this->hasMany(AssetLocation::class);
    }

    public function assetsOwned(): HasMany
    {
        return $this->hasMany(Asset::class, 'department_owner_id');
    }
}
