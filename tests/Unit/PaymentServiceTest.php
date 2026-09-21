<?php

namespace Tests\Unit;

use App\Enums\PlanType;
use App\Models\Member;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_expires_same_day(): void
    {
        $service = new PaymentService;
        $paidAt = Carbon::parse('2026-01-15');

        $validUntil = $service->calculateValidUntil(PlanType::Visit, $paidAt);

        $this->assertTrue($validUntil->isSameDay($paidAt));
    }

    public function test_monthly_plan_adds_one_month_without_overflow(): void
    {
        $service = new PaymentService;
        $paidAt = Carbon::parse('2026-01-31');

        $validUntil = $service->calculateValidUntil(PlanType::MonthDay, $paidAt);

        // Jan 31 + 1 month must not overflow into March.
        $this->assertTrue($validUntil->isSameDay(Carbon::parse('2026-02-28')));
    }

    public function test_yearly_plan_adds_one_year(): void
    {
        $service = new PaymentService;
        $paidAt = Carbon::parse('2026-03-10');

        $validUntil = $service->calculateValidUntil(PlanType::Year, $paidAt);

        $this->assertTrue($validUntil->isSameDay(Carbon::parse('2027-03-10')));
    }

    public function test_new_payment_before_expiry_extends_from_current_expiry_not_today(): void
    {
        $service = new PaymentService;
        $member = Member::factory()->create();

        $firstPaidAt = Carbon::today()->subDays(10);
        $service->recordPayment($member, PlanType::MonthDay, 2500, $firstPaidAt);

        $secondPaidAt = Carbon::today();
        $second = $service->recordPayment($member, PlanType::MonthDay, 2500, $secondPaidAt);

        $expectedBase = $firstPaidAt->copy()->addMonthNoOverflow();
        $this->assertTrue($second->valid_until->isSameDay($expectedBase->copy()->addMonthNoOverflow()));
    }

    public function test_payment_after_expiry_starts_fresh_from_payment_date(): void
    {
        $service = new PaymentService;
        $member = Member::factory()->create();

        $firstPaidAt = Carbon::today()->subMonthNoOverflow()->subDays(5);
        $service->recordPayment($member, PlanType::MonthDay, 2500, $firstPaidAt);

        $secondPaidAt = Carbon::today();
        $second = $service->recordPayment($member, PlanType::MonthDay, 2500, $secondPaidAt);

        $this->assertTrue($second->valid_until->isSameDay($secondPaidAt->copy()->addMonthNoOverflow()));
    }
}
