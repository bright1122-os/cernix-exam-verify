<?php

namespace App\Support;

final class Roles
{
    public const SUPER_ADMIN = 'SUPER_ADMIN';
    public const ADMIN = 'ADMIN';
    public const EXAMINER = 'EXAMINER';

    public static function normalize(?string $role): string
    {
        return strtoupper((string) $role);
    }

    public static function isAdminLike(?string $role): bool
    {
        return in_array(self::normalize($role), [self::SUPER_ADMIN, self::ADMIN], true);
    }
}
