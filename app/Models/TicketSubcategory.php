<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_category_id',
        'code',
        'name',
        'description',
        'requires_approval',
        'allow_direct_assignment',
        'approver_user_id',
        'approver_strategy',
        'approver_role_code',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'allow_direct_assignment' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function engineerSkills(): BelongsToMany
    {
        return $this->belongsToMany(EngineerSkill::class, 'engineer_skill_ticket_subcategory')
            ->withTimestamps();
    }

    public function detailSubcategories(): HasMany
    {
        return $this->hasMany(TicketDetailSubcategory::class, 'ticket_subcategory_id');
    }
}
