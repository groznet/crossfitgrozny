<?php

namespace Database\Seeders;

use App\Enums\PlanType;
use App\Models\Member;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MemberDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the 10 members required for visually testing the main list
     * (mixed statuses), plus a couple of pending requests and one archived
     * member so every screen has something to show.
     */
    public function run(): void
    {
        $paymentService = app(PaymentService::class);

        // ~3 clearly active members (20-40 days left).
        Member::factory()->count(3)->create()->each(function (Member $member) use ($paymentService) {
            $paidAt = Carbon::today()->subDays(rand(1, 10));
            $paymentService->recordPayment($member, PlanType::MonthDay, 2500, $paidAt);
        });

        // ~2 expiring soon (0-5 days left).
        Member::factory()->count(2)->create()->each(function (Member $member) use ($paymentService) {
            $daysLeft = rand(0, 5);
            $paidAt = Carbon::today()->subMonthNoOverflow()->addDays($daysLeft);
            $paymentService->recordPayment($member, PlanType::MonthEvening, 3500, $paidAt);
        });

        // ~3 expired (5-30 days in the past).
        Member::factory()->count(3)->create()->each(function (Member $member) use ($paymentService) {
            $paidAt = Carbon::today()->subMonthNoOverflow()->subDays(rand(5, 30));
            $paymentService->recordPayment($member, PlanType::MonthDay, 2500, $paidAt);
        });

        // ~2 with no payments at all ("Нет оплат").
        Member::factory()->count(2)->create();

        // A couple of pending public-form submissions for the New Requests screen.
        Member::factory()->pending()->count(2)->create();

        // One archived member.
        Member::factory()->archived()->create();
    }
}
