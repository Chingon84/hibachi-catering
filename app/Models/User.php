<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
    }

    public function isOwner(): bool
    {
        return ($this->role ?? '') === 'owner';
    }

    public function hasRole(string|array $roles): bool
    {
        $r = $this->role ?? '';
        if (is_array($roles)) return in_array($r, $roles, true);
        return $r === $roles;
    }

    public function permissions(): array
    {
        if ($this->isOwner()) return ['*'];
        // Prefer DB mapping if table exists, fallback to config
        try {
            if (class_exists(RolePermission::class)) {
                $list = RolePermission::query()
                    ->where('role', $this->role)
                    ->pluck('permission')
                    ->all();
                if (!empty($list)) return $list;
            }
        } catch (\Throwable $e) {
            // likely table not migrated yet; ignore
        }
        $map = config('permissions.roles');
        return $map[$this->role] ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) return true;
        $perms = $this->permissions();
        if (in_array('*', $perms, true)) return true;
        return in_array($permission, $perms, true);
    }
}
