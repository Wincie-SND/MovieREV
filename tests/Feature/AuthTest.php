<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('username', 50)->unique();
            $table->enum('usertype', ['admin', 'user'])->default('user');
            $table->timestamps();
        });
    }

    public function test_login_page_loads_for_guests(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Email or Username')
            ->assertSee('Password');
    }

    public function test_register_page_loads_for_guests(): void
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('Create your account')
            ->assertSee('Confirm Password');
    }

    public function test_registering_an_account_persists_it_and_signs_the_user_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Brand New',
            'username' => 'brand_new',
            'email' => 'brand_new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Successfully created an account');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Brand New',
            'username' => 'brand_new',
            'email' => 'brand_new@example.com',
            'usertype' => 'user',
        ]);
    }

    public function test_registering_an_account_does_not_set_a_persistent_cookie(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Signup',
            'username' => 'new_signup',
            'email' => 'new_signup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
    }

    public function test_home_page_loads_dashboard_for_guests(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('MovieREV home', false)
            ->assertSee('Recently Released')
            ->assertSee('Log In')
            ->assertSee('Sign Up');
    }

    public function test_home_page_sign_up_button_points_at_the_register_form(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('register').'"', false);
    }

    public function test_logout_requires_an_authenticated_user(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }

    public function test_login_without_remember_me_does_not_set_a_persistent_cookie(): void
    {
        $this->createUser();

        $response = $this->post('/login', [
            'identifier' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $recaller = Auth::guard('web')->getRecallerName();
        try {
            $response->assertCookieExpired($recaller);
        } catch (\Throwable $e) {
            $response->assertCookieMissing($recaller);
        }
    }

    public function test_login_with_remember_me_sets_a_persistent_cookie(): void
    {
        $this->createUser();

        $response = $this->post('/login', [
            'identifier' => 'user@example.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $response->assertCookie(Auth::guard('web')->getRecallerName());
    }

    public function test_remember_me_is_the_only_thing_that_survives_a_browser_restart(): void
    {
        // Tests run on the `array` session driver, which never emits a
        // session cookie, so this guards the setting rather than the cookie:
        // with expire_on_close off (Laravel's default) an unticked
        // "Remember me" still stays signed in for SESSION_LIFETIME minutes.
        $this->assertTrue(
            config('session.expire_on_close'),
            'The session cookie must expire when the browser closes, otherwise unchecking "Remember me" does nothing.',
        );
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Login User',
            'email' => 'user@example.com',
            'password' => 'password',
            'username' => 'login_user',
            'usertype' => 'user',
        ]);
    }
}
