<?php

namespace App\Enums;

enum UserRole: string
{
    case PLATFORM_ADMIN = 'platform_admin';
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case GENERAL_USER = 'general_user';
    case STAFF = 'staff';
    case GUEST = 'guest';

    /**
     * Get the display label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::PLATFORM_ADMIN => 'Platform Admin',
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::GENERAL_USER => 'General User',
            self::STAFF => 'Staff',
            self::GUEST => 'Guest',
        };
    }

    /**
     * Get the hierarchy level (higher = more permissions)
     */
    public function level(): int
    {
        return match($this) {
            self::PLATFORM_ADMIN => 5,
            self::SUPER_ADMIN => 4,
            self::ADMIN => 3,
            self::GENERAL_USER => 2,
            self::STAFF => 1,
            self::GUEST => 0,
        };
    }

    /**
     * Check if this role can manage another role
     */
    public function canManage(UserRole $targetRole): bool
    {
        return $this->level() >= $targetRole->level();
    }

    /**
     * Get all roles that this role can manage
     */
    public function managableRoles(): array
    {
        return array_filter(
            self::cases(),
            fn(UserRole $role) => $this->level() >= $role->level()
        );
    }

    /**
     * Get all roles as associative array [value => label]
     */
    public static function toSelectArray(): array
    {
        $result = [];
        foreach (self::cases() as $role) {
            $result[$role->value] = $role->label();
        }
        return $result;
    }

    /**
     * Check if role can manage users
     */
    public function canManageUsers(): bool
    {
        return in_array($this, [self::PLATFORM_ADMIN, self::SUPER_ADMIN, self::ADMIN]);
    }
}

