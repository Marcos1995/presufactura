<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_records_view_without_pii(): void
    {
        $this->get('/')->assertOk();

        $this->assertDatabaseHas('analytics_events', [
            'name' => AnalyticsEvent::LANDING_VIEW,
            'source' => AnalyticsEvent::SOURCE_LANDING,
        ]);

        $row = AnalyticsEvent::first();
        $this->assertNotNull($row);
        $this->assertNull($row->http_status);
        $payload = json_encode($row->getAttributes());
        $this->assertStringNotContainsString('@', $payload);
        $this->assertStringNotContainsString('NIF', $payload);
    }

    public function test_signup_cta_is_whitelisted_and_unknown_events_are_rejected(): void
    {
        $this->postJson('/a/e', ['name' => AnalyticsEvent::SIGNUP_CTA_CLICK])->assertOk();
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::SIGNUP_CTA_CLICK]);

        $this->postJson('/a/e', ['name' => 'steal_email'])->assertStatus(422);
        $this->assertDatabaseMissing('analytics_events', ['name' => 'steal_email']);
    }

    public function test_bots_are_tagged_and_404_is_recorded(): void
    {
        $this->get('/', ['User-Agent' => 'Googlebot/2.1'])->assertOk();
        $this->assertDatabaseHas('analytics_events', [
            'name' => AnalyticsEvent::LANDING_VIEW,
            'source' => AnalyticsEvent::SOURCE_BOT,
        ]);

        $this->get('/ruta-que-no-existe')->assertNotFound();
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::HTTP_4XX]);
    }

    public function test_registration_funnel_events(): void
    {
        $this->get('/registro')->assertOk();
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::REGISTRATION_STARTED]);

        $this->post('/registro', [
            'name' => 'Usuario Test',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::REGISTRATION_COMPLETED]);
        $event = AnalyticsEvent::where('name', AnalyticsEvent::REGISTRATION_COMPLETED)->first();
        $this->assertStringNotContainsString('nuevo@example.com', json_encode($event->getAttributes()));
    }

    public function test_funnel_page_is_restricted_to_demo_admin(): void
    {
        $user = User::factory()->onboarded()->create();
        $this->actingAs($user)->get('/embudo')->assertForbidden();
    }
}
