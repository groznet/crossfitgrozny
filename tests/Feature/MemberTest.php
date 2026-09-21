<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Enums\PlanType;
use App\Models\Member;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('members.index'))->assertRedirect(route('login'));
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
}
