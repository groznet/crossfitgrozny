<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_a_payment(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();

        $response = $this->post(route('payments.store', $member), [
            'plan' => 'month_day',
            'amount' => 2500,
            'paid_at' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('members.show', $member));
        $this->assertCount(1, $member->payments()->get());

        $payment = $member->payments()->first();
        $this->assertSame(2500, $payment->amount);
        $this->assertTrue($payment->valid_until->isSameDay(Carbon::today()->addMonthNoOverflow()));
    }

    public function test_admin_can_edit_a_payment(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();
        $payment = Payment::factory()->for($member)->create();

        $response = $this->put(route('payments.update', $payment), [
            'plan' => 'visit',
            'amount' => 400,
            'paid_at' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('members.show', $member));
        $this->assertSame(400, $payment->fresh()->amount);
    }

    public function test_admin_can_delete_a_payment(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();
        $payment = Payment::factory()->for($member)->create();

        $response = $this->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('members.show', $member));
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_future_paid_at_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();

        $response = $this->post(route('payments.store', $member), [
            'plan' => 'month_day',
            'amount' => 2500,
            'paid_at' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('paid_at');
    }
}
