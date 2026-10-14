<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
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

    public function test_home_page_loads_dashboard_for_guests(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('MovieREV home', false)
            ->assertSee('Recently Reviewed')
            ->assertSee('Log In');
    }

    public function test_logout_requires_an_authenticated_user(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }
}
