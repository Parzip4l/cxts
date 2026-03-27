<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    public const APPROVER_STRATEGY_FALLBACK = 'fallback';
    public const APPROVER_STRATEGY_SPECIFIC_USER = 'specific_user';
    public const APPROVER_STRATEGY_REQUESTER_DEPARTMENT_HEAD = 'requester_department_head';
    public const APPROVER_STRATEGY_SERVICE_MANAGER = 'service_manager';
    public const APPROVER_STRATEGY_ROLE_BASED = 'role_based';

    protected $fillable = [
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

    public function subcategories(): HasMany
    {
        return $this->hasMany(TicketSubcategory::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public static function approverStrategies(): array
    {
        return [
            self::APPROVER_STRATEGY_FALLBACK => 'Supervisor/Admin Fallback',
            self::APPROVER_STRATEGY_SPECIFIC_USER => 'Specific User',
            self::APPROVER_STRATEGY_REQUESTER_DEPARTMENT_HEAD => 'Requester Department Head',
            self::APPROVER_STRATEGY_SERVICE_MANAGER => 'Service Manager',
            self::APPROVER_STRATEGY_ROLE_BASED => 'Role Based',
        ];
    }

    public static function approverRoleOptions(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'operational_admin' => 'Operational Admin',
            'supervisor' => 'Supervisor',
        ];
    }

    public static function approverRoleLabel(?string $roleCode): ?string
    {
        if ($roleCode === null || $roleCode === '') {
            return null;
        }

        return static::approverRoleOptions()[$roleCode]
            ?? str($roleCode)->replace('_', ' ')->title()->toString();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
