<?php

namespace App\Support;

final class Rupiah
{
    /** Keep the exact editable value of older transactions with fractional rupiah. */
    public static function input(mixed $value): string
    {
        return preg_replace('/\.0+$/D', '', (string) $value);
    }
}
