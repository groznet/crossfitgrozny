<?php

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Enums\PreferredTime;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => '+7'.$this->faker->numerify('9#########'),
            'photo_url' => null,
            'birth_date' => $this->faker->optional(0.6)->dateTimeBetween('-55 years', '-16 years')?->format('Y-m-d'),
            'preferred_time' => $this->faker->optional(0.6)->randomElement(PreferredTime::cases()),
            'note' => $this->faker->optional(0.3)->sentence(),
            'status' => MemberStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Pending]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Archived]);
    }
}
