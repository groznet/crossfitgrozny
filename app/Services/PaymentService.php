<?php

namespace App\Services;

use App\Enums\PlanType;
use App\Models\Member;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class PaymentService
{
    /**
     * Calculate the expiry date for a plan, counted from the given base date.
     */
    public function calculateValidUntil(PlanType $plan, CarbonInterface $base): Carbon
    {
        return match ($plan) {
            PlanType::Visit => Carbon::instance($base)->copy(),
            PlanType::MonthDay, PlanType::MonthEvening => Carbon::instance($base)->copy()->addMonthNoOverflow(),
            PlanType::Year => Carbon::instance($base)->copy()->addYearNoOverflow(),
        };
    }

    /**
     * A new payment extends from the member's current expiry date if that
     * expiry hasn't passed yet; otherwise it starts fresh from the payment date.
     */
    public function baseDateForNewPayment(Member $member, CarbonInterface $paidAt, ?int $excludePaymentId = null): Carbon
    {
        $currentExpiry = $member->payments()
            ->when($excludePaymentId, fn ($query) => $query->where('id', '!=', $excludePaymentId))
            ->where('valid_until', '>=', $paidAt->toDateString())
            ->orderByDesc('valid_until')
            ->value('valid_until');

        return $currentExpiry ? Carbon::parse($currentExpiry) : Carbon::instance($paidAt)->copy();
    }

    public function recordPayment(Member $member, PlanType $plan, int $amount, CarbonInterface $paidAt): Payment
    {
        $base = $this->baseDateForNewPayment($member, $paidAt);
        $validUntil = $this->calculateValidUntil($plan, $base);

        return $member->payments()->create([
            'plan' => $plan,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'valid_until' => $validUntil,
        ]);
    }

    public function recalculate(Payment $payment, PlanType $plan, int $amount, CarbonInterface $paidAt): Payment
    {
        $base = $this->baseDateForNewPayment($payment->member, $paidAt, $payment->id);
        $validUntil = $this->calculateValidUntil($plan, $base);

        $payment->update([
            'plan' => $plan,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'valid_until' => $validUntil,
        ]);

        return $payment;
    }
}
