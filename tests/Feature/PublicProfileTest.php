<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_a_profile_request(): void
    {
        $response = $this->post(route('public.profile.store'), [
            'full_name' => 'Новый Участник',
            'phone' => '+7 963 123-45-67',
        ]);

        $response->assertRedirect(route('public.profile.thanks'));

        $member = Member::sole();
        $this->assertSame(MemberStatus::Pending, $member->status);
        $this->assertSame('+79631234567', $member->phone);
    }

    public function test_filled_honeypot_pretends_success_but_saves_nothing(): void
    {
        $response = $this->post(route('public.profile.store'), [
            'full_name' => 'Bot',
            'phone' => '+79630000000',
            'company' => 'Acme Corp',
        ]);

        $response->assertRedirect(route('public.profile.thanks'));
        $this->assertDatabaseCount('members', 0);
    }

    public function test_full_name_and_phone_are_required(): void
    {
        $response = $this->post(route('public.profile.store'), []);

        $response->assertSessionHasErrors(['full_name', 'phone']);
    }
}
