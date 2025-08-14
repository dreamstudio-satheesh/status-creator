<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email', 
        'password',
        'role',
        'is_active',
        'last_login_at',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users') || $this->isAdmin();
    }

    public function canManageContent(): bool
    {
        return $this->hasPermission('manage_content') || $this->isAdmin();
    }

    public function canViewAnalytics(): bool
    {
        return $this->hasPermission('view_analytics') || $this->isAdmin();
    }

    public function getDisplayRoleAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'moderator' => 'Moderator',
            default => 'Unknown'
        };
    }
}
