<?php

namespace Tests\Feature;

use App\Enums\PlanType;
use App\Enums\ReceiptStatus;
use App\Models\Member;
use App\Models\PaymentReceipt;
use App\Models\PlanPrice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        User::factory()->create();
        PlanPrice::factory()->create(['plan' => PlanType::MonthDay, 'price' => 2500]);
        Setting::setValue('payment_phone', '+7 900 000-00-00');
    }

    public function test_member_page_shows_transfer_details(): void
    {
        $member = Member::factory()->create();

        $this->get(route('public.member.show', $member->public_token))
            ->assertOk()
            ->assertSee('+7 900 000-00-00')
            ->assertSee(__('receipts.pay_submit'));
    }

    public function test_payment_block_is_hidden_until_transfer_phone_is_set(): void
    {
        Setting::setValue('payment_phone', null);
        $member = Member::factory()->create();

        $this->get(route('public.member.show', $member->public_token))
            ->assertOk()
            ->assertDontSee(__('receipts.pay_submit'));
    }

    public function test_member_can_upload_a_receipt_without_creating_a_payment(): void
    {
        $member = Member::factory()->create();

        $this->post(route('public.member.receipts.store', $member->public_token), [
            'plan' => 'month_day',
            'receipt' => UploadedFile::fake()->image('check.jpg'),
        ])->assertRedirect(route('public.member.show', $member->public_token));

        $receipt = $member->paymentReceipts()->first();
        $this->assertSame(ReceiptStatus::Pending, $receipt->status);
        $this->assertSame(2500, $receipt->amount);
        Storage::disk('local')->assertExists($receipt->file_path);
        $this->assertCount(0, $member->payments);
    }

    public function test_receipt_requires_a_valid_file(): void
    {
        $member = Member::factory()->create();

        $this->post(route('public.member.receipts.store', $member->public_token), [
            'plan' => 'month_day',
            'receipt' => UploadedFile::fake()->create('note.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('receipt');

        $this->assertCount(0, $member->paymentReceipts);
    }

    public function test_only_one_pending_receipt_at_a_time(): void
    {
        $member = Member::factory()->create();
        PaymentReceipt::factory()->for($member)->create();

        $this->post(route('public.member.receipts.store', $member->public_token), [
            'plan' => 'month_day',
            'receipt' => UploadedFile::fake()->image('check.jpg'),
        ])->assertSessionHasErrors('plan');

        $this->assertCount(1, $member->paymentReceipts);
    }

    public function test_non_active_member_cannot_upload_a_receipt(): void
    {
        $member = Member::factory()->pending()->create();

        $this->post(route('public.member.receipts.store', $member->public_token), [
            'plan' => 'month_day',
            'receipt' => UploadedFile::fake()->image('check.jpg'),
        ])->assertForbidden();
    }

    public function test_admin_confirming_a_receipt_records_the_payment(): void
    {
        $this->actingAs(User::first());
        $member = Member::factory()->create();
        $receipt = PaymentReceipt::factory()->for($member)->create();

        $this->post(route('receipts.confirm', $receipt))->assertRedirect(route('receipts.index'));

        $receipt->refresh();
        $this->assertSame(ReceiptStatus::Confirmed, $receipt->status);
        $payment = $member->payments()->first();
        $this->assertSame($payment->id, $receipt->payment_id);
        $this->assertSame(2500, $payment->amount);
        $this->assertTrue($payment->valid_until->isSameDay(Carbon::today()->addMonthNoOverflow()));
    }

    public function test_confirming_twice_does_not_duplicate_the_payment(): void
    {
        $this->actingAs(User::first());
        $receipt = PaymentReceipt::factory()->create();

        $this->post(route('receipts.confirm', $receipt));
        $this->post(route('receipts.confirm', $receipt));

        $this->assertCount(1, $receipt->member->payments);
    }

    public function test_admin_can_reject_a_receipt(): void
    {
        $this->actingAs(User::first());
        $receipt = PaymentReceipt::factory()->create();

        $this->post(route('receipts.reject', $receipt))->assertRedirect(route('receipts.index'));

        $this->assertSame(ReceiptStatus::Rejected, $receipt->fresh()->status);
        $this->assertCount(0, $receipt->member->payments);
    }

    public function test_receipt_files_and_queue_are_admin_only(): void
    {
        $receipt = PaymentReceipt::factory()->create();

        $this->get(route('receipts.index'))->assertRedirect(route('public.community.index'));
        $this->get(route('receipts.file', $receipt))->assertRedirect(route('public.community.index'));
        $this->post(route('receipts.confirm', $receipt))->assertRedirect(route('public.community.index'));
    }

    public function test_admin_can_view_the_queue_and_receipt_file(): void
    {
        $this->actingAs(User::first());
        Storage::disk('local')->put('receipts/a.jpg', 'x');
        $receipt = PaymentReceipt::factory()->create(['file_path' => 'receipts/a.jpg']);

        $this->get(route('receipts.index'))->assertOk()->assertSee($receipt->member->full_name);
        $this->get(route('receipts.file', $receipt))->assertOk();
    }

    public function test_admin_can_update_payment_details(): void
    {
        $this->actingAs(User::first());

        $this->put(route('settings.payment-details.update'), [
            'payment_phone' => '+7 911 111-11-11',
            'payment_bank' => 'Сбербанк',
            'payment_recipient' => 'Магомед А.',
        ])->assertRedirect(route('settings.prices.edit'));

        $this->assertSame('+7 911 111-11-11', Setting::getValue('payment_phone'));
        $this->assertSame('Сбербанк', Setting::getValue('payment_bank'));
        $this->get(route('settings.prices.edit'))->assertOk()->assertSee('Сбербанк');
    }
}
