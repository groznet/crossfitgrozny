<?php

namespace Tests\Feature;

use App\Enums\PlanType;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_plan_prices(): void
    {
        $this->actingAs(User::factory()->create());
        PlanPrice::factory()->createMany([
            ['plan' => PlanType::Visit, 'price' => 400],
            ['plan' => PlanType::MonthDay, 'price' => 2500],
            ['plan' => PlanType::MonthEvening, 'price' => 3500],
            ['plan' => PlanType::Year, 'price' => 25000],
        ]);

        $response = $this->put(route('settings.prices.update'), [
            'prices' => [
                'visit' => 450,
                'month_day' => 2700,
                'month_evening' => 3700,
                'year' => 26000,
            ],
        ]);

        $response->assertRedirect(route('settings.prices.edit'));
        $this->assertSame(450, PlanPrice::where('plan', PlanType::Visit)->value('price'));
        $this->assertSame(2700, PlanPrice::where('plan', PlanType::MonthDay)->value('price'));
    }

    public function test_updating_a_price_does_not_change_past_payments(): void
    {
        $this->actingAs(User::factory()->create());
        PlanPrice::factory()->create(['plan' => PlanType::MonthDay, 'price' => 2500]);
        $payment = Payment::factory()->for(Member::factory())->create(['plan' => PlanType::MonthDay, 'amount' => 2500]);

        $this->put(route('settings.prices.update'), [
            'prices' => [
                'visit' => 400,
                'month_day' => 3000,
                'month_evening' => 3500,
                'year' => 25000,
            ],
        ]);

        $this->assertSame(2500, $payment->fresh()->amount);
    }
}
