<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'employee_number',
        'password',
        'role',
        'tenant_id',
        'biometric_consent_granted',
        'biometric_consent_timestamp',
        'biometric_consent_ip',
        'policy_version_agreed',
        'expires_at',
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
            'biometric_consent_granted' => 'boolean',
            'biometric_consent_timestamp' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Validation rule for ZOU email
    public static function rules()
    {
        return [
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'employee_number' => ['required', 'string', Rule::unique('users')->whereNull('deleted_at')],
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
        return $this->role === UserRole::PLATFORM_ADMIN || (is_string($this->role) && $this->role === 'platform_admin') || (isset($this->role->value) && $this->role->value === 'platform_admin');
    }

    public function isSuperAdmin(): bool
    {
        $roleValue = is_string($this->role) ? $this->role : ($this->role->value ?? null);
        return in_array($roleValue, ['super_admin', 'platform_admin'], true) || in_array($this->role, [UserRole::SUPER_ADMIN, UserRole::PLATFORM_ADMIN]);
    }

    public function isAdmin(): bool
    {
        $roleValue = is_string($this->role) ? $this->role : ($this->role->value ?? null);
        return in_array($roleValue, ['admin', 'super_admin', 'platform_admin'], true) || in_array($this->role, [UserRole::ADMIN, UserRole::SUPER_ADMIN, UserRole::PLATFORM_ADMIN]);
    }

    public function isGeneralUser(): bool
    {
        return $this->role === UserRole::GENERAL_USER || (is_string($this->role) && $this->role === 'general_user') || (isset($this->role->value) && $this->role->value === 'general_user');
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::STAFF || (is_string($this->role) && $this->role === 'staff') || (isset($this->role->value) && $this->role->value === 'staff');
    }

    public function isGuest(): bool
    {
        return $this->role === UserRole::GUEST || (is_string($this->role) && $this->role === 'guest') || (isset($this->role->value) && $this->role->value === 'guest');
    }

    public function canManageUsers(): bool
    {
        if ($this->role instanceof UserRole) {
            return $this->role->canManageUsers();
        }
        return $this->isAdmin(); // Fallback for string roles
    }

    public function getRoleLabel(): string
    {
        if ($this->role instanceof UserRole) {
            return $this->role->label();
        }
        
        $labels = [
            'platform_admin' => 'Platform Admin',
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'general_user' => 'General User',
            'staff' => 'Staff',
            'guest' => 'Guest',
        ];
        $val = is_string($this->role) ? $this->role : ($this->role->value ?? '');
        return $labels[$val] ?? 'Unknown';
    }
}