<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_log_in_with_username(): void
    {
        User::factory()->create([
            'username' => 'adam',
            'phone' => '+79639892011',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'adam',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_admin_can_log_in_with_phone(): void
    {
        User::factory()->create([
            'username' => 'adam',
            'phone' => '+79639892011',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => '+79639892011',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_invalid_password_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'adam',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'adam',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_community_from_protected_route(): void
    {
        $this->post(route('logout'))->assertRedirect(route('public.community.index'));
    }
}
