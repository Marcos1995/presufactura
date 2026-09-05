<?php

namespace Tests;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }
    protected function createClient(User $user, array $attributes = []): Client
    {
        return $user->clients()->create(array_merge([
            'name' => 'Cliente Test',
            'email' => 'cliente@example.com',
        ], $attributes));
    }

    protected function createDocument(User $user, Client $client, array $attributes = []): Document
    {
        return $user->documents()->create(array_merge([
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'FAC-'.fake()->unique()->numerify('####'),
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => Str::random(32),
        ], $attributes));
    }

    protected function invoicePayload(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'lines' => [
                [
                    'description' => 'Servicio de consultoría',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 21,
                ],
            ],
        ];
    }

    protected function quotePayload(int $clientId): array
    {
        return [
            'client_id' => $clientId,
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(15)->toDateString(),
            'lines' => [
                [
                    'description' => 'Servicio de consultoría',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 21,
                ],
            ],
        ];
    }

    protected function activateVerifactuCertificate(User $user): void
    {
        $company = $user->ensureDefaultCompany();
        $company->ensureSifConfig();
        $config = $company->fresh()->sifConfig ?? $user->sifConfig;
        $config->update([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'mode' => \App\Models\UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
            'cert_path' => 'sif/certs/company_'.$company->id.'.p12.enc',
            'cert_expires_at' => now()->addYear(),
        ]);
        $user->unsetRelation('sifConfig');
        $company->unsetRelation('sifConfig');
    }
}
