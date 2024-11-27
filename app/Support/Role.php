<?php

namespace App\Support;

class Role
{
    public const USER = 'USER';
    public const HR = 'HR';
    public const ADMIN = 'ADMIN';

    /**
     * Get all roles.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::USER,
            self::HR,
            self::ADMIN,
        ];
    }
}