<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_blocks_fourth_document_in_month(): void
    {
        $user = User::factory()->onboarded()->create(['plan' => 'free']);
        $client = $this->createClient($user);

        for ($i = 0; $i < 3; $i++) {
            $this->createDocument($user, $client, [
                'number' => 'FAC-00'.$i,
            ]);
        }

        $this->actingAs($user);

        $response = $this->get(route('invoices.create'));

        $response->assertRedirect();
        $response->assertSessionHas('show_upgrade_modal', true);
        $this->assertSame(3, $user->fresh()->documentsThisMonthCount());
    }
}
