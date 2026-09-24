<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Enums\PlanType;
use App\Models\Member;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_community(): void
    {
        $this->get(route('members.index'))->assertRedirect(route('public.community.index'));
    }

    public function test_list_shows_only_active_members_by_default(): void
    {
        $this->actingAs(User::factory()->create());

        $active = Member::factory()->create();
        $archived = Member::factory()->archived()->create();
        $pending = Member::factory()->pending()->create();

        $response = $this->get(route('members.index'));

        $response->assertSee($active->full_name);
        $response->assertDontSee($archived->full_name);
        $response->assertDontSee($pending->full_name);
    }

    public function test_counters_reflect_computed_subscription_status(): void
    {
        $this->actingAs(User::factory()->create());
        $service = app(PaymentService::class);

        $active = Member::factory()->create();
        $service->recordPayment($active, PlanType::MonthDay, 2500, Carbon::today());

        $expired = Member::factory()->create();
        $service->recordPayment($expired, PlanType::MonthDay, 2500, Carbon::today()->subMonthsNoOverflow(2));

        $noPayments = Member::factory()->create();

        $response = $this->get(route('members.index'));

        $response->assertViewHas('counts', function ($counts) {
            return $counts->get('active') === 1
                && $counts->get('expired') === 1
                && $counts->get('none') === 1;
        });
    }

    public function test_search_filters_by_name(): void
    {
        $this->actingAs(User::factory()->create());

        $match = Member::factory()->create(['full_name' => 'Иван Иванов']);
        $other = Member::factory()->create(['full_name' => 'Пётр Петров']);

        $response = $this->get(route('members.index', ['search' => 'Иванов']));

        $response->assertSee($match->full_name);
        $response->assertDontSee($other->full_name);
    }

    public function test_admin_can_view_and_edit_a_member(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();

        $this->get(route('members.show', $member))->assertOk()->assertSee($member->full_name);

        $response = $this->put(route('members.update', $member), [
            'full_name' => 'Новое Имя',
            'phone' => $member->phone,
        ]);

        $response->assertRedirect(route('members.show', $member));
        $this->assertSame('Новое Имя', $member->fresh()->full_name);
    }

    public function test_admin_can_archive_and_restore_a_member(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create();

        $this->post(route('members.archive', $member))->assertRedirect(route('members.index'));
        $this->assertSame(MemberStatus::Archived, $member->fresh()->status);

        $this->post(route('members.restore', $member))->assertRedirect(route('members.show', $member));
        $this->assertSame(MemberStatus::Active, $member->fresh()->status);
    }

    public function test_member_page_repairs_a_missing_public_token(): void
    {
        $this->actingAs(User::factory()->create());

        $member = Member::factory()->create();
        $member->forceFill(['public_token' => null])->saveQuietly();

        $this->get(route('members.show', $member))->assertOk();

        $this->assertNotNull($member->fresh()->public_token);
    }

    public function test_home_sends_guests_to_community(): void
    {
        $this->get('/')->assertRedirect(route('public.community.index'));
    }

    public function test_home_sends_admin_to_member_list(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertRedirect(route('members.index'));
    }

    public function test_edit_form_offers_to_remove_an_existing_photo(): void
    {
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create(['photo_url' => 'photos/current.jpg']);

        $this->get(route('members.edit', $member))
            ->assertOk()
            ->assertSee('name="remove_photo"', false);
    }

    public function test_admin_can_remove_a_members_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/current.jpg', 'image');
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create(['photo_url' => 'photos/current.jpg']);

        $this->put(route('members.update', $member), [
            'full_name' => $member->full_name,
            'phone' => $member->phone,
            'remove_photo' => '1',
        ])->assertRedirect(route('members.show', $member));

        $this->assertNull($member->fresh()->photo_url);
        Storage::disk('public')->assertMissing('photos/current.jpg');
    }

    public function test_photo_is_kept_when_remove_is_not_checked(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/current.jpg', 'image');
        $this->actingAs(User::factory()->create());
        $member = Member::factory()->create(['photo_url' => 'photos/current.jpg']);

        $this->put(route('members.update', $member), [
            'full_name' => $member->full_name,
            'phone' => $member->phone,
        ]);

        $this->assertSame('photos/current.jpg', $member->fresh()->photo_url);
        Storage::disk('public')->assertExists('photos/current.jpg');
    }
}
