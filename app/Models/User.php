<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\RolePermission;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'position',
        'phone',
        'staff_type',
        'role',
        'can_access_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'can_access_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOperationalType($query, string $type)
    {
        return $query->where('staff_type', $type);
    }

    public function isOwner(): bool
    {
        return $this->normalizedRole() === 'owner';
    }

    public function hasRole(string|array $roles): bool
    {
        $r = $this->normalizedRole();
        if (is_array($roles)) {
            $normalized = array_map(fn ($role) => strtolower((string) $role), $roles);
            return in_array($r, $normalized, true);
        }
        return $r === strtolower((string) $roles);
    }

    public function isAdminPrincipal(): bool
    {
        $byFlag = (int) ($this->can_access_admin ?? 0) === 1;
        $byRole = $this->hasRole(['owner', 'admin']);

        return $byFlag || $byRole;
    }

    public function baseAdminViewPermissions(): array
    {
        return config('permissions.admin_base_view_permissions', []);
    }

    public function permissions(): array
    {
        if ($this->isOwner()) return ['*'];

        $role = $this->normalizedRole();
        $baseViews = $this->isAdminPrincipal() ? $this->baseAdminViewPermissions() : [];

        // Prefer DB mapping if table exists, fallback to config
        try {
            if (class_exists(RolePermission::class)) {
                $list = RolePermission::query()
                    ->where('role', $role)
                    ->pluck('permission')
                    ->all();
                if (!empty($list)) {
                    return array_values(array_unique(array_merge($list, $baseViews)));
                }
            }
        } catch (\Throwable $e) {
            // likely table not migrated yet; ignore
        }
        $map = config('permissions.roles');
        $rolePerms = $map[$role] ?? [];

        return array_values(array_unique(array_merge($rolePerms, $baseViews)));
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) return true;
        $perms = $this->permissions();
        if (in_array('*', $perms, true)) return true;
        return in_array($permission, $perms, true);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeamMemberDocument::class, 'team_member_id');
    }

    public function teamActivities(): HasMany
    {
        return $this->hasMany(TeamMemberActivity::class, 'team_member_id');
    }

    private function normalizedRole(): string
    {
        return strtolower((string) ($this->role ?? ''));
    }
}
