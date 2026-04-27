<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_employees_page_returns_success(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
