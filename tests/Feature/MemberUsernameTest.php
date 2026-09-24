<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_is_reachable_by_id_until_a_username_is_set(): void
    {
        $member = Member::factory()->create(['full_name' => 'Иван Иванов']);

        $this->assertSame(url('/u/'.$member->id), $member->profileUrl());
        $this->get($member->profileUrl())->assertOk()->assertSee('Иван Иванов');
    }

    public function test_public_profile_never_reveals_subscription_status(): void
    {
        $member = Member::factory()->create();

        $response = $this->get($member->profileUrl());

        $response->assertOk();
        $response->assertDontSee($member->subscription_status->label());
    }

    public function test_pending_and_archived_members_have_no_public_profile(): void
    {
        $pending = Member::factory()->pending()->create();
        $archived = Member::factory()->archived()->create();

        $this->get('/u/'.$pending->id)->assertNotFound();
        $this->get('/u/'.$archived->id)->assertNotFound();
    }

    public function test_member_can_set_a_username_from_their_private_page(): void
    {
        $member = Member::factory()->create();

        $response = $this->post(route('public.member.username', $member->public_token), ['username' => ' Magomed_95 ']);

        $response->assertRedirect($member->publicUrl());
        $this->assertSame('magomed_95', $member->fresh()->username);
        $this->get('/u/magomed_95')->assertOk()->assertSee($member->full_name);
    }

    public function test_id_url_redirects_to_username_url_once_set(): void
    {
        $member = Member::factory()->create();
        $member->forceFill(['username' => 'adam'])->save();

        $this->get('/u/'.$member->id)->assertRedirect('/u/adam');
    }

    public function test_username_that_is_taken_is_rejected(): void
    {
        Member::factory()->create()->forceFill(['username' => 'adam'])->save();
        $member = Member::factory()->create();

        $response = $this->post(route('public.member.username', $member->public_token), ['username' => 'ADAM']);

        $response->assertSessionHasErrors(['username' => __('public.username_taken')]);
        $this->assertNull($member->fresh()->username);
    }

    public function test_member_can_resubmit_their_own_current_username(): void
    {
        $member = Member::factory()->create();
        $member->forceFill(['username' => 'adam'])->save();

        $response = $this->post(route('public.member.username', $member->public_token), ['username' => 'adam']);

        $response->assertSessionHasNoErrors();
    }

    public function test_invalid_usernames_are_rejected(): void
    {
        $member = Member::factory()->create();

        foreach (['12345', 'ab', 'иван', 'john doe', 'a-b-c'] as $username) {
            $this->post(route('public.member.username', $member->public_token), ['username' => $username])
                ->assertSessionHasErrors('username');
        }

        $this->assertNull($member->fresh()->username);
    }

    public function test_pending_member_cannot_set_a_username(): void
    {
        $member = Member::factory()->pending()->create();

        $this->post(route('public.member.username', $member->public_token), ['username' => 'adam'])
            ->assertForbidden();
    }

    public function test_private_page_shows_the_username_form_for_active_members(): void
    {
        User::factory()->create(['phone' => '+79639892011']);
        $member = Member::factory()->create();

        $this->get($member->publicUrl())
            ->assertSee(route('public.member.username', $member->public_token), false)
            ->assertSee($member->profileUrl(), false);
    }

    public function test_community_cards_link_to_public_profiles(): void
    {
        $member = Member::factory()->create();

        $this->get(route('public.community.index'))->assertSee($member->profileUrl(), false);
    }

    public function test_join_date_is_the_first_payment_date(): void
    {
        $member = Member::factory()->create(['created_at' => '2026-09-01']);
        Payment::factory()->for($member)->create(['paid_at' => '2025-06-10']);
        Payment::factory()->for($member)->create(['paid_at' => '2025-03-05']);

        $this->assertSame('2025-03-05', $member->joined_at->toDateString());
        $this->get($member->profileUrl())->assertSee(__('public.member_since', ['date' => 'март 2025']));
    }

    public function test_join_date_falls_back_to_created_at_without_payments(): void
    {
        $member = Member::factory()->create(['created_at' => '2025-11-20 12:00:00']);

        $this->assertSame('2025-11-20', $member->joined_at->toDateString());
        $this->get($member->profileUrl())->assertSee(__('public.member_since', ['date' => 'ноябрь 2025']));
    }

    public function test_editing_a_profile_does_not_change_the_join_date(): void
    {
        $this->actingAs(User::factory()->create());
        $this->travelTo('2025-04-15');
        $member = Member::factory()->create();
        Payment::factory()->for($member)->create(['paid_at' => '2025-04-15']);
        $this->travelTo('2026-09-23');

        $this->put(route('members.update', $member), [
            'full_name' => 'Новое Имя',
            'phone' => $member->phone,
        ])->assertRedirect(route('members.show', $member));

        $member = $member->fresh();
        $this->assertSame('2026-09-23', $member->updated_at->toDateString());
        $this->assertSame('2025-04-15', $member->joined_at->toDateString());
        $this->get($member->profileUrl())
            ->assertSee(__('public.member_since', ['date' => 'апрель 2025']))
            ->assertDontSee('сентябрь 2026');
    }
}
