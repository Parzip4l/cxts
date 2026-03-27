<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EngineerSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function engineers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'engineer_skill_user')
            ->withTimestamps();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCatalog::class, 'engineer_skill_service', 'engineer_skill_id', 'service_id')
            ->withTimestamps();
    }

    public function ticketSubcategories(): BelongsToMany
    {
        return $this->belongsToMany(TicketSubcategory::class, 'engineer_skill_ticket_subcategory')
            ->withTimestamps();
    }

    public function ticketDetailSubcategories(): BelongsToMany
    {
        return $this->belongsToMany(TicketDetailSubcategory::class, 'engineer_skill_ticket_detail_subcategory')
            ->withTimestamps();
    }

    public function assetCategories(): BelongsToMany
    {
        return $this->belongsToMany(AssetCategory::class, 'asset_category_engineer_skill')
            ->withTimestamps();
    }
}
