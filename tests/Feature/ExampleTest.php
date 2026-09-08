<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_does_not_expose_a_webinar(): void
    {
        $response = $this->get('/');

        $response->assertNotFound();
    }
}
