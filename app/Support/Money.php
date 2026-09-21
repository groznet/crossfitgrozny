<?php

namespace App\Support;

class Money
{
    /**
     * Format an integer RUB amount for display, e.g. 3500 -> "3 500 ₽".
     */
    public static function format(int $rub): string
    {
        return number_format($rub, 0, '', ' ').' ₽';
    }
}
