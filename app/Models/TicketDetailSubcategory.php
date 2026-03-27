<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketDetailSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_subcategory_id',
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
        return $this->belongsTo(TicketSubcategory::class, 'ticket_subcategory_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'ticket_detail_subcategory_id');
    }

    public function engineerSkills(): BelongsToMany
    {
        return $this->belongsToMany(EngineerSkill::class, 'engineer_skill_ticket_detail_subcategory')
            ->withTimestamps();
    }
}
