<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_shows_only_active_members(): void
    {
        $active = Member::factory()->create(['full_name' => 'Активный Участник']);
        $pending = Member::factory()->pending()->create(['full_name' => 'Ожидающий Участник']);
        $archived = Member::factory()->archived()->create(['full_name' => 'Архивный Участник']);

        $response = $this->get(route('public.community.index'));

        $response->assertOk();
        $response->assertSee($active->full_name);
        $response->assertDontSee($pending->full_name);
        $response->assertDontSee($archived->full_name);
    }

    public function test_directory_does_not_reveal_subscription_status(): void
    {
        $member = Member::factory()->create();

        $response = $this->get(route('public.community.index'));

        $response->assertOk();
        $response->assertDontSee(__('members.status_active'));
        $response->assertDontSee(__('members.status_expiring'));
        $response->assertDontSee(__('members.status_expired'));
        $response->assertDontSee(__('members.status_none'));
    }

    public function test_directory_is_public(): void
    {
        $response = $this->get(route('public.community.index'));

        $response->assertOk();
    }

    public function test_directory_links_to_the_join_form(): void
    {
        $response = $this->get(route('public.community.index'));

        $response->assertSee(route('public.profile.create'), false);
    }
}
