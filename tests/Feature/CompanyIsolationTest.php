<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_company_does_not_see_first_company_clients(): void
    {
        $user = User::factory()->onboarded()->create();
        $first = $user->currentCompany();
        $client = $this->createClient($user, ['name' => 'Cliente A']);

        $second = $user->companies()->create([
            'legal_name' => 'Otra SL',
            'tax_id' => 'B11111111',
            'country' => 'ES',
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $this->post(route('companies.switch', $second))->assertRedirect();

        $this->get(route('clients.index'))
            ->assertOk()
            ->assertDontSee('Cliente A');
    }

    public function test_user_cannot_switch_to_another_users_company(): void
    {
        $owner = User::factory()->onboarded()->create();
        $stranger = User::factory()->onboarded()->create();
        $company = $owner->currentCompany();

        $this->actingAs($stranger)
            ->post(route('companies.switch', $company))
            ->assertForbidden();
    }

    public function test_user_can_create_a_second_company(): void
    {
        $user = User::factory()->onboarded()->create();

        $this->actingAs($user)
            ->post(route('companies.store'), [
                'legal_name' => 'Segunda SL',
                'tax_id' => 'B12345678',
                'country' => 'ES',
                'vat_regime' => 'general',
                'default_vat_rate' => 21,
                'default_due_days' => 30,
                'invoice_prefix' => 'FAC',
                'quote_prefix' => 'PRE',
                'accept_terms' => '1',
            ])
            ->assertRedirect(route('settings.index'));

        $this->assertSame(2, $user->companies()->count());
        $this->assertSame('Segunda SL', $user->fresh()->currentCompany()->legal_name);
    }
}
