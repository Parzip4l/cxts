<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roleRef(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'code');
    }

    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_user_id');
    }

    public function managedServices(): HasMany
    {
        return $this->hasMany(ServiceCatalog::class, 'service_manager_user_id');
    }

    public function engineerSchedules(): HasMany
    {
        return $this->hasMany(EngineerSchedule::class, 'user_id');
    }

    public function assignedEngineerSchedules(): HasMany
    {
        return $this->hasMany(EngineerSchedule::class, 'assigned_by_id');
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(UserPushToken::class);
    }

    public function engineerSkills(): BelongsToMany
    {
        return $this->belongsToMany(EngineerSkill::class, 'engineer_skill_user')
            ->withTimestamps();
    }

    public function permissionList(): array
    {
        if (class_exists(\App\Models\Permission::class) && \Illuminate\Support\Facades\Schema::hasTable('permissions') && \Illuminate\Support\Facades\Schema::hasTable('permission_role')) {
            $role = $this->relationLoaded('roleRef')
                ? $this->roleRef
                : $this->roleRef()->first();

            if ($role !== null) {
                $permissions = $role->relationLoaded('permissions')
                    ? $role->permissions
                    : $role->permissions()->where('permissions.is_active', true)->get(['permissions.code']);

                $codes = collect($permissions)
                    ->pluck('code')
                    ->filter()
                    ->map(fn ($code) => (string) $code)
                    ->unique()
                    ->values()
                    ->all();

                return $codes;
            }
        }

        $permissions = config('rbac.role_defaults.' . $this->role, []);

        return array_values(array_unique(array_map('strval', $permissions)));
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissionList();

        return in_array('*', $permissions, true)
            || in_array($permission, $permissions, true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }
}
