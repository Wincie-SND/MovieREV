<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_page_loads_for_guests(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('Username or Email');
    }

    public function test_register_page_loads_for_guests(): void
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('Create Account')
            ->assertSee('Confirm Password');
    }

    public function test_home_page_links_to_login_and_register_for_guests(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Sign In')
            ->assertSee('Get Started!');
    }

    public function test_logout_requires_an_authenticated_user(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }
}
