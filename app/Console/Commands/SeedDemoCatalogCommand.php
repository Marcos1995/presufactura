<?php

namespace App\Console\Commands;

use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\BillingRecordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class SeedDemoCatalogCommand extends Command
{
    protected $signature = 'presufactura:seed-demo';

    protected $description = 'Genera documentos de ejemplo (presupuesto, factura, Veri*Factu) solo para DEMO_ADMIN_EMAIL';

    public function handle(BillingRecordService $billing): int
    {
        $email = config('demo.admin_email');
        if (! filled($email)) {
            $this->error('Define DEMO_ADMIN_EMAIL en .env con el único usuario que debe ver el catálogo.');

            return self::FAILURE;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $this->error('No hay ningún usuario con ese email. Créalo primero y completa el onboarding.');

            return self::FAILURE;
        }

        if (! $user->isDemoAdmin() || ! filled($user->tax_id) || ! $user->onboarding_completed_at) {
            $this->error('El usuario demo debe coincidir con DEMO_ADMIN_EMAIL y tener NIF y onboarding.');

            return self::FAILURE;
        }

        $client = Client::query()->firstOrCreate(
            ['user_id' => $user->id, 'email' => 'catalogo-demo@presufactura.invalid'],
            ['name' => 'Cliente catálogo demo', 'tax_id' => 'B87654321']
        );

        if (Document::query()->where('user_id', $user->id)->where('number', 'DEMO-P-001')->exists()) {
            $this->info('El catálogo de ejemplo ya existe para '.$user->email);

            return self::SUCCESS;
        }

        $this->makeDocument($user, $client, [
            'type' => Document::TYPE_QUOTE,
            'number' => 'DEMO-P-001',
            'status' => Document::STATUS_ACCEPTED,
            'accepted_at' => now()->subDays(2),
            'sent_at' => now()->subDays(3),
        ]);

        $this->makeDocument($user, $client, [
            'type' => Document::TYPE_INVOICE,
            'number' => 'DEMO-F-PRO',
            'status' => Document::STATUS_SENT,
            'sent_at' => now()->subDay(),
        ]);

        $paid = $this->makeDocument($user, $client, [
            'type' => Document::TYPE_INVOICE,
            'number' => 'DEMO-F-PAG',
            'status' => Document::STATUS_PAID,
            'sent_at' => now()->subDays(10),
            'paid_at' => now()->subDays(4),
        ]);

        $fiscal = $this->makeDocument($user, $client, [
            'type' => Document::TYPE_INVOICE,
            'number' => 'DEMO-F-FIS',
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
        ]);

        Queue::fake();
        $user->load('sifConfig');
        $config = $user->sifConfig;
        $originalPath = $config?->cert_path;
        $originalExpiry = $config?->cert_expires_at;

        if ($config) {
            $config->update([
                'enabled' => true,
                'mode' => \App\Models\UserSifConfig::MODE_VERIFACTU,
                'cert_path' => $originalPath ?: 'sif/certs/demo-catalog.p12.enc',
                'cert_expires_at' => $originalExpiry ?: now()->addYear(),
            ]);
        }

        $alta = $billing->createAltaRecord($fiscal->fresh(['user.sifConfig', 'client', 'lineItems']));

        if ($config && ! $originalPath) {
            $config->update([
                'cert_path' => null,
                'cert_expires_at' => null,
            ]);
        }

        $this->line('Cliente: '.$client->name);
        $this->line('Presupuesto aceptado: DEMO-P-001');
        $this->line('Factura proforma: DEMO-F-PRO');
        $this->line('Factura pagada: '.$paid->number);
        $this->line('Factura Veri*Factu (XML+hash, sin AEAT): '.($alta instanceof BillingRecord ? 'DEMO-F-FIS' : 'no creada'));
        $this->info('Catálogo de ejemplo listo para '.$user->email);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeDocument(User $user, Client $client, array $attributes): Document
    {
        $document = Document::create(array_merge([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => Str::random(32),
        ], $attributes));

        $document->lineItems()->create([
            'description' => 'Servicio de ejemplo PresuFactura',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        return $document;
    }
}
