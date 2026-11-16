<?php

namespace App\Support;

class Pagination
{
    public const OPTIONS = [10, 15, 20, 50];

    public static function perPage(): int
    {
        $requested = (string) request()->query('per_page', '10');

        if ($requested === 'all') {
            return 1000;
        }

        $value = (int) $requested;

        return in_array($value, self::OPTIONS, true) ? $value : 10;
    }
}
