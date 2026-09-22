<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression test: DatabaseSeeder must NOT use WithoutModelEvents, since
     * seeded members rely on Member::booted()'s creating hook to get a
     * public_token. Losing that silently breaks every seeded member's
     * /m/{token} link and admin page (Member::publicUrl() throws when the
     * token is null).
     */
    public function test_seeded_members_all_get_a_public_token(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, Member::count());
        $this->assertSame(0, Member::whereNull('public_token')->count());
    }
}
