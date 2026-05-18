<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'name',
        'email',
        'employee_number',
        'password',
        'role',
        'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class, // Cast to enum
        ];
    }

    // Validation rule for ZOU email
    public static function rules()
    {
        return [
            'email' => 'required|email|unique:users',
            'employee_number' => 'required|string|unique:users',
            'name' => 'required|string|max:255',
            'password' => 'required|min:8|confirmed',
        ];
    }

    // Relationships
    public function biometricData()
    {
        return $this->hasOne(BiometricData::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // Role helper methods - now delegate to enum
    public function isPlatformAdmin(): bool
    {
        return $this->role === UserRole::PLATFORM_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        // PLATFORM_ADMIN is also treated as super admin within any tenant context
        return in_array($this->role, [UserRole::SUPER_ADMIN, UserRole::PLATFORM_ADMIN]);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::ADMIN, UserRole::SUPER_ADMIN, UserRole::PLATFORM_ADMIN]);
    }

    public function isGeneralUser(): bool
    {
        return $this->role === UserRole::GENERAL_USER;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::STAFF;
    }

    public function canManageUsers(): bool
    {
        return $this->role->canManageUsers();
    }

    public function getRoleLabel(): string
    {
        return $this->role->label();
    }
}