<?php

namespace App\Support;

final class Money
{
    public static function of(int|float|string $value): string
    {
        if (is_float($value)) {
            return number_format($value, 2, '.', '');
        }

        $normalized = is_int($value) ? (string) $value : trim((string) $value);
        if ($normalized === '') {
            return '0.00';
        }

        return bcadd($normalized, '0', 2);
    }

    public static function add(int|float|string $left, int|float|string $right): string
    {
        return bcadd(self::of($left), self::of($right), 2);
    }

    public static function sub(int|float|string $left, int|float|string $right): string
    {
        return bcsub(self::of($left), self::of($right), 2);
    }

    public static function mul(int|float|string $left, int|float|string $right, int $scale = 8): string
    {
        return bcadd(bcmul(self::of($left), self::of($right), $scale), '0', 2);
    }

    public static function percent(int|float|string $base, int|float|string $rate): string
    {
        $factor = bcdiv(self::of($rate), '100', 8);

        return bcadd(bcmul(self::of($base), $factor, 8), '0', 2);
    }

    public static function toFloat(int|float|string $value): float
    {
        return (float) self::of($value);
    }
}
