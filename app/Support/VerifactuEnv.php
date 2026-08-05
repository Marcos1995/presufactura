<?php

namespace App\Support;

class VerifactuEnv
{
    public static function current(): string
    {
        return config('verifactu.env', 'preprod');
    }

    public static function isPreprod(): bool
    {
        return self::current() === 'preprod';
    }

    public static function isProd(): bool
    {
        return self::current() === 'prod';
    }

    public static function label(): string
    {
        return config('verifactu.env_labels.'.self::current(), self::current());
    }

    public static function badgeClass(): string
    {
        return self::isPreprod() ? 'badge-env-preprod' : 'badge-env-prod';
    }
}
