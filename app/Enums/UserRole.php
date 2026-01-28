<?php

namespace App\Enums;

/**
 * UserRole Enum - Definisi role user dalam sistem
 *
 * Enum ini bertujuan untuk menyediakan konstanta role yang konsisten
 * di seluruh aplikasi untuk menghindari hardcoded strings dan typo errors
 */
enum UserRole: string
{
    case SUPERADMIN = 'SUPERADMIN';
    case ADMIN = 'ADMIN';
    case PRINCIPAL = 'PRINCIPAL';
    case TEACHER = 'TEACHER';
    case PARENT = 'PARENT';
    case STUDENT = 'STUDENT';

    /**
     * Get array of admin roles (SUPERADMIN dan ADMIN)
     *
     * @return array<string>
     */
    public static function adminRoles(): array
    {
        return [
            self::SUPERADMIN->value,
            self::ADMIN->value,
        ];
    }

    /**
     * Check apakah role termasuk admin role
     *
     * @param  string  $role  Role string untuk dicek
     * @return bool True jika role adalah admin
     */
    public static function isAdmin(string $role): bool
    {
        return in_array($role, self::adminRoles());
    }
}
