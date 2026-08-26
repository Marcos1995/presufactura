<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_reminders_sends_client_notice_on_configured_day(): void
    {
        $user = User::factory()->onboarded()->create([
            'reminder_day_1' => 3,
        ]);
        $client = $this->createClient($user);
        $this->createDocument($user, $client, [
            'status' => Document::STATUS_SENT,
            'sent_at' => now()->subDays(4),
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        Artisan::call('presufactura:process-reminders');

        $this->assertDatabaseHas('reminders', [
            'type' => Reminder::TYPE_CLIENT_DAY_3,
        ]);
    }
}
