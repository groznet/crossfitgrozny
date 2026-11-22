<?php

namespace Database\Factories;

use App\Enums\PlanType;
use App\Enums\ReceiptStatus;
use App\Models\Member;
use App\Models\PaymentReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentReceipt>
 */
class PaymentReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'plan' => PlanType::MonthDay,
            'amount' => 2500,
            'file_path' => 'receipts/'.$this->faker->uuid().'.jpg',
            'status' => ReceiptStatus::Pending,
        ];
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => ReceiptStatus::Rejected, 'reviewed_at' => now()]);
    }
}
