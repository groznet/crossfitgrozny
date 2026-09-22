<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_get_a_unique_token_automatically(): void
    {
        $a = Member::factory()->create();
        $b = Member::factory()->create();

        $this->assertNotEmpty($a->public_token);
        $this->assertNotEmpty($b->public_token);
        $this->assertNotSame($a->public_token, $b->public_token);
    }

    public function test_visiting_a_members_public_link_shows_their_status(): void
    {
        User::factory()->create(['phone' => '+79639892011']);
        $member = Member::factory()->create(['full_name' => 'Иван Иванов']);

        $response = $this->get($member->publicUrl());

        $response->assertOk();
        $response->assertSee('Иван Иванов');
        $response->assertSee($member->subscription_status->label());
    }

    public function test_pending_member_sees_a_pending_notice_instead_of_status(): void
    {
        User::factory()->create(['phone' => '+79639892011']);
        $member = Member::factory()->pending()->create();

        $response = $this->get($member->publicUrl());

        $response->assertOk();
        $response->assertSee(__('public.member_pending_notice'));
    }

    public function test_unknown_token_404s(): void
    {
        $response = $this->get('/m/does-not-exist');

        $response->assertNotFound();
    }

    public function test_public_profile_links_to_contact_adam_on_whatsapp(): void
    {
        User::factory()->create(['phone' => '+79639892011']);
        $member = Member::factory()->create();

        $response = $this->get($member->publicUrl());

        $response->assertSee('https://wa.me/79639892011', false);
    }
}
