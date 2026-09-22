<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['sms.ru/*' => Http::response(['status' => 'OK'])]);
    }

    private function otpEntry(string $phone): ?array
    {
        return Cache::get("profile-otp:{$phone}");
    }

    public function test_visitor_can_submit_and_verify_a_profile_request(): void
    {
        $sendResponse = $this->post(route('public.profile.send-code'), [
            'full_name' => 'Новый Участник',
            'phone' => '+7 963 123-45-67',
        ]);

        $sendResponse->assertOk();
        Http::assertSent(fn ($request) => str_contains($request->url(), 'sms.ru'));

        $entry = $this->otpEntry('+79631234567');
        $this->assertNotNull($entry);
        $this->assertSame(0, $entry['attempts']);

        $verifyResponse = $this->post(route('public.profile.verify-code'), [
            'phone' => '+79631234567',
            'code' => $entry['code'],
        ]);

        $verifyResponse->assertRedirect(route('public.profile.thanks'));

        $member = Member::sole();
        $this->assertSame(MemberStatus::Pending, $member->status);
        $this->assertSame('+79631234567', $member->phone);
        $this->assertNull($this->otpEntry('+79631234567'));
    }

    public function test_wrong_code_is_rejected_and_does_not_create_a_member(): void
    {
        $this->post(route('public.profile.send-code'), [
            'full_name' => 'Иван Иванов',
            'phone' => '+79997654321',
        ]);

        $response = $this->post(route('public.profile.verify-code'), [
            'phone' => '+79997654321',
            'code' => '0000',
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('members', 0);

        $entry = $this->otpEntry('+79997654321');
        $this->assertSame(1, $entry['attempts']);
    }

    public function test_five_wrong_attempts_invalidates_the_code(): void
    {
        $this->post(route('public.profile.send-code'), [
            'full_name' => 'Иван Иванов',
            'phone' => '+79997654321',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('public.profile.verify-code'), [
                'phone' => '+79997654321',
                'code' => '0000',
            ]);
        }
        $this->assertNotNull($this->otpEntry('+79997654321'));

        $response = $this->post(route('public.profile.verify-code'), [
            'phone' => '+79997654321',
            'code' => '0000',
        ]);

        $response->assertSee(__('public.too_many_attempts'));
        $this->assertNull($this->otpEntry('+79997654321'));
    }

    public function test_resend_respects_cooldown(): void
    {
        $this->post(route('public.profile.send-code'), [
            'full_name' => 'Иван Иванов',
            'phone' => '+79997654321',
        ]);

        $firstCode = $this->otpEntry('+79997654321')['code'];

        $response = $this->post(route('public.profile.resend-code'), [
            'phone' => '+79997654321',
        ]);

        $response->assertSee(__('public.resend_too_soon'));
        $this->assertSame($firstCode, $this->otpEntry('+79997654321')['code']);
    }

    public function test_filled_honeypot_pretends_success_and_sends_no_sms(): void
    {
        $response = $this->post(route('public.profile.send-code'), [
            'full_name' => 'Bot',
            'phone' => '+79630000000',
            'company' => 'Acme Corp',
        ]);

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertNull($this->otpEntry('+79630000000'));
        $this->assertDatabaseCount('members', 0);
    }

    public function test_full_name_and_phone_are_required(): void
    {
        $response = $this->post(route('public.profile.send-code'), []);

        $response->assertSessionHasErrors(['full_name', 'phone']);
    }

    public function test_send_code_is_rate_limited_per_phone(): void
    {
        $payload = ['full_name' => 'Иван Иванов', 'phone' => '+79997654321'];

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('public.profile.send-code'), $payload)->assertOk();
        }

        $this->post(route('public.profile.send-code'), $payload)->assertStatus(429);
    }
}
