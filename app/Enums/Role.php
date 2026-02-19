<?php

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'SuperAdmin';
    case SALES = 'Sales';
    case PURCHASE = 'Purchase';
    case MANAGER = 'Manager';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::SALES => 'Sales Staff',
            self::PURCHASE => 'Purchasing Staff',
            self::MANAGER => 'Manager',
        };
    }
}
