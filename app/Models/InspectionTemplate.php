<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'asset_category_id',
        'is_active',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionTemplateItem::class)->orderBy('sequence');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
