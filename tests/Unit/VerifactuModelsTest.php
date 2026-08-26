<?php

namespace Tests\Unit;

use App\Models\BillingRecord;
use App\Models\SifEvent;
use App\Models\User;
use App\Models\UserSifConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifactuModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifactu_config_loads_software_and_urls(): void
    {
        $this->assertSame('PresuFactura', config('verifactu.software.name'));
        $this->assertArrayHasKey('preprod', config('verifactu.qr_urls'));
        $this->assertArrayHasKey('preprod', config('verifactu.wsdl'));
    }

    public function test_user_sif_config_relationship(): void
    {
        $user = User::factory()->create();
        $config = $user->sifConfig;

        $this->assertNotNull($config);
        $this->assertTrue($config->enabled);
        $this->assertSame(UserSifConfig::MODE_VERIFACTU, $config->mode);
        $this->assertTrue($user->hasVerifactuEnabled());
    }

    public function test_billing_record_belongs_to_document_and_user(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $document = $this->createDocument($user, $client);

        $record = BillingRecord::factory()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'hash_current' => str_repeat('a', 64),
        ]);

        $this->assertTrue($document->fresh()->billingRecord->is($record));
        $this->assertSame(BillingRecord::TYPE_ALTA, $record->record_type);
        $this->assertSame(BillingRecord::STATUS_PENDING, $record->aeat_status);
    }

    public function test_sif_event_stores_payload_json(): void
    {
        $user = User::factory()->create();

        $event = SifEvent::factory()->for($user)->create([
            'event_type' => SifEvent::TYPE_EXPORT,
            'payload' => ['count' => 3],
        ]);

        $this->assertSame(['count' => 3], $event->fresh()->payload);
        $this->assertCount(1, $user->fresh()->sifEvents);
    }
}
