<?php

namespace Database\Factories;

use App\Enums\PlanType;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = $this->faker->randomElement(PlanType::cases());
        $paidAt = Carbon::instance($this->faker->dateTimeBetween('-2 months', 'now'));

        return [
            'plan' => $plan,
            'amount' => $this->defaultPriceFor($plan),
            'paid_at' => $paidAt,
            'valid_until' => app(PaymentService::class)->calculateValidUntil($plan, $paidAt),
        ];
    }

    /**
     * Force the payment's valid_until to land a given number of days from today.
     */
    public function expiringInDays(int $days): static
    {
        return $this->state(function () use ($days) {
            $paidAt = Carbon::today()->subMonthNoOverflow();
            $validUntil = Carbon::today()->addDays($days);

            return [
                'plan' => PlanType::MonthDay,
                'amount' => $this->defaultPriceFor(PlanType::MonthDay),
                'paid_at' => $paidAt,
                'valid_until' => $validUntil,
            ];
        });
    }

    private function defaultPriceFor(PlanType $plan): int
    {
        return match ($plan) {
            PlanType::Visit => 400,
            PlanType::MonthDay => 2500,
            PlanType::MonthEvening => 3500,
            PlanType::Year => 25000,
        };
    }
}
