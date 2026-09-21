<?php

namespace Database\Factories;

use App\Enums\PlanType;
use App\Models\PlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanPrice>
 */
class PlanPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan' => $this->faker->randomElement(PlanType::cases()),
            'price' => $this->faker->numberBetween(400, 25000),
        ];
    }
}
