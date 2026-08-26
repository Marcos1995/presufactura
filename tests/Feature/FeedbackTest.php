<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_appears_after_first_document_and_can_be_saved(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $this->createDocument($user, $client);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('¿Qué esperabas encontrar?');

        $this->post(route('feedback.store'), [
            'expected' => 'Un PDF rápido',
            'difficult' => 'Nada',
            'used_before' => 'Excel',
            'missing_weekly' => 'Nada',
            'would_recommend' => 9,
        ])->assertRedirect();

        $this->assertDatabaseHas('user_feedback', [
            'user_id' => $user->id,
            'dismissed' => 0,
            'would_recommend' => 9,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('¿Qué esperabas encontrar?');
    }
}
