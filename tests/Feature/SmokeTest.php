<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_200(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_pricing_page_returns_200(): void
    {
        $this->get('/precios')->assertOk();
    }

    public function test_help_page_returns_200(): void
    {
        $this->get('/ayuda')->assertOk();
    }
}
