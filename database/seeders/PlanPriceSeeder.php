<?php

namespace Database\Seeders;

use App\Enums\PlanType;
use App\Models\PlanPrice;
use Illuminate\Database\Seeder;

class PlanPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
            PlanType::Visit->value => 400,
            PlanType::MonthDay->value => 2500,
            PlanType::MonthEvening->value => 3500,
            PlanType::Year->value => 25000,
        ];

        foreach ($prices as $plan => $price) {
            PlanPrice::updateOrCreate(['plan' => $plan], ['price' => $price]);
        }
    }
}
