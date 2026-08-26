<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoGuidesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_guides_have_unique_titles_and_are_in_sitemap(): void
    {
        $titles = [];
        foreach (array_keys(config('guides')) as $slug) {
            $html = $this->get(route('guides.show', $slug))->assertOk()->getContent();
            preg_match('/<title>(.*?)<\/title>/s', $html, $m);
            $titles[] = $m[1] ?? '';
            $this->assertStringContainsString('Probar PresuFactura', $html);
            $this->assertStringContainsString('index, follow', $html);
        }

        $this->assertSame(count($titles), count(array_unique($titles)));

        $this->get(route('guides.index'))->assertOk()->assertSee('Guías para autónomos');

        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('/guias', $sitemap);
        $this->assertStringNotContainsString('/login', $sitemap);
        $this->assertStringNotContainsString('/dashboard', $sitemap);

        $robots = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Disallow: /login', $robots);
        $this->assertStringContainsString('Disallow: /registro', $robots);
        $this->assertStringContainsString('Allow: /guias', $robots);
    }

    public function test_auth_pages_are_noindex(): void
    {
        $this->get('/login')->assertOk()->assertSee('noindex', false);
        $this->get('/registro')->assertOk()->assertSee('noindex', false);
    }

    public function test_brand_assets_are_present(): void
    {
        $this->assertFileExists(public_path('images/logo-icon.svg'));
        $this->assertFileExists(public_path('images/favicon.svg'));
        $this->assertFileExists(public_path('images/og-presufactura.svg'));
        $this->assertFileExists(public_path('images/favicon.ico'));
        $this->assertFileExists(public_path('favicon.ico'));
    }

    public function test_error_pages_do_not_leak_traces(): void
    {
        $html = $this->get('/no-existe-esta-ruta')->assertNotFound()->getContent();
        $this->assertStringContainsString('Página no encontrada', $html);
        $this->assertStringNotContainsString('Stack trace', $html);
        $this->assertStringNotContainsString('Illuminate\\', $html);
    }
}
