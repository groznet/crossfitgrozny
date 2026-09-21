<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize a Russian phone number to E.164, e.g. "8 963 123-45-67" -> "+79631234567".
     */
    public static function normalize(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7'.substr($digits, 1);
        }

        return '+'.$digits;
    }
}
