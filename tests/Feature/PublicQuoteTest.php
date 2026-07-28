<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_quote_can_be_accepted(): void
    {
        Mail::fake();

        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $quote = $this->createDocument($user, $client, [
            'type' => Document::TYPE_QUOTE,
            'number' => 'PRE-0001',
            'status' => Document::STATUS_SENT,
            'valid_until' => now()->addDays(7)->toDateString(),
            'due_date' => null,
            'public_token' => 'token-aceptar-test',
        ]);

        $response = $this->post(route('quotes.public.accept', ['token' => $quote->public_token]));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $quote->refresh();
        $this->assertSame(Document::STATUS_ACCEPTED, $quote->status);
        $this->assertNotNull($quote->accepted_at);
    }
}
