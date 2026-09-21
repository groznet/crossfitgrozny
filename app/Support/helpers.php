<?php

use Illuminate\Support\Carbon;

if (! function_exists('format_date')) {
    /**
     * Format a date for display in the Russian DD.MM.YYYY convention.
     */
    function format_date(Carbon|string|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        return Carbon::parse($date)->format('d.m.Y');
    }
}
