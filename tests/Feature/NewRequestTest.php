<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_flags_a_duplicate_phone(): void
    {
        $this->actingAs(User::factory()->create());

        $existing = Member::factory()->create(['phone' => '+79631234567']);
        $pending = Member::factory()->pending()->create(['phone' => '+79631234567']);

        $response = $this->get(route('requests.index'));

        $response->assertViewHas('pending', function ($pending) use ($existing) {
            return $pending->first()->duplicateOf?->is($existing);
        });
    }

    public function test_admin_can_approve_a_pending_member(): void
    {
        $this->actingAs(User::factory()->create());
        $pending = Member::factory()->pending()->create();

        $response = $this->put(route('requests.approve', $pending), [
            'full_name' => $pending->full_name,
            'phone' => $pending->phone,
        ]);

        $response->assertRedirect(route('members.show', $pending));
        $this->assertSame(MemberStatus::Active, $pending->fresh()->status);
    }

    public function test_admin_can_reject_a_pending_member(): void
    {
        $this->actingAs(User::factory()->create());
        $pending = Member::factory()->pending()->create();

        $response = $this->delete(route('requests.reject', $pending));

        $response->assertRedirect(route('requests.index'));
        $this->assertDatabaseMissing('members', ['id' => $pending->id]);
    }

    public function test_admin_can_merge_a_pending_member_into_an_existing_one(): void
    {
        $this->actingAs(User::factory()->create());
        $target = Member::factory()->create(['note' => null]);
        $pending = Member::factory()->pending()->create(['note' => 'Занимается 2 года']);

        $response = $this->post(route('requests.merge', [$pending, $target]));

        $response->assertRedirect(route('members.show', $target));
        $this->assertSame('Занимается 2 года', $target->fresh()->note);
        $this->assertDatabaseMissing('members', ['id' => $pending->id]);
    }
}
