<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_displays_the_virtual_portal_landing_page(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('VirtualPortal - Live Event Platform')
            ->assertSee('One powerful portal for your')
            ->assertSee('+91 99041 11866')
            ->assertSee('info@evoqraeventsmedia.com')
            ->assertDontSee('class="lead-form"', false);
    }
}
