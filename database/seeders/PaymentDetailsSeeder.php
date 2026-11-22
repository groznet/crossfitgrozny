<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PaymentDetailsSeeder extends Seeder
{
    /**
     * Seed placeholder transfer details; Magomed edits them in Settings.
     * Existing values are never overwritten.
     */
    public function run(): void
    {
        if (Setting::getValue('payment_phone') === null) {
            Setting::setValue('payment_phone', '+7 963 989-20-11');
        }
    }
}
