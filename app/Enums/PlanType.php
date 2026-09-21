<?php

namespace App\Enums;

enum PlanType: string
{
    case Visit = 'visit';
    case MonthDay = 'month_day';
    case MonthEvening = 'month_evening';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Visit => __('payments.plan_visit'),
            self::MonthDay => __('payments.plan_month_day'),
            self::MonthEvening => __('payments.plan_month_evening'),
            self::Year => __('payments.plan_year'),
        };
    }
}
