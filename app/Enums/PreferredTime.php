<?php

namespace App\Enums;

enum PreferredTime: string
{
    case Day = 'day';
    case Evening = 'evening';

    public function label(): string
    {
        return match ($this) {
            self::Day => __('members.preferred_time_day'),
            self::Evening => __('members.preferred_time_evening'),
        };
    }
}
