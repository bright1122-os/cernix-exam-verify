<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Student Portal')
            ->assertSee('Examiner Login')
            ->assertSee('Admin Panel');
    }
}
