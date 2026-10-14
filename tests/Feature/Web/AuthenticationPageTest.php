<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class AuthenticationPageTest extends TestCase
{
    public function test_login_page_is_modern_and_does_not_expose_demo_credentials(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('Masuk ke workspace')
            ->assertSee('toggle-password')
            ->assertDontSee('Demo Accounts')
            ->assertDontSee('name="password" value=', false);
    }
}
