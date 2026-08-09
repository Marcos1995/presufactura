<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_users_can_create_documents_without_monthly_limit(): void
    {
        $user = User::factory()->onboarded()->create(['plan' => 'free']);
        $client = $this->createClient($user);

        for ($i = 0; $i < 3; $i++) {
            $this->createDocument($user, $client, [
                'number' => 'FAC-00'.$i,
            ]);
        }

        $this->actingAs($user);

        $this->get(route('invoices.create'))->assertOk();
        $this->assertTrue($user->canCreateDocument());
        $this->assertTrue($user->isPro());
    }
}
