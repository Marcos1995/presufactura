<?php

namespace App\Console\Commands;

use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\BillingRecordService;
use App\Support\VerifactuSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class VerifactuProveCommand extends Command
{
    protected $signature = 'presufactura:verifactu-prove';

    protected $description = 'Demuestra el flujo Veri*Factu (registro, proforma sin certificado, SIF con certificado) y deshace los datos';

    public function handle(BillingRecordService $billing): int
    {
        if (! VerifactuSchema::hasSifConfigTable() || ! VerifactuSchema::hasBillingRecordsTable()) {
            $this->error('Faltan tablas Veri*Factu. Ejecuta php artisan migrate.');

            return self::FAILURE;
        }

        Queue::fake();
        $ok = true;

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => 'Prueba VeriFactu',
                'email' => 'prove-'.Str::lower(Str::random(8)).'@example.test',
                'password' => 'password',
                'business_name' => 'Demo VeriFactu SL',
                'tax_id' => '89890001K',
                'iban' => 'ES9121000418450200051332',
                'onboarding_completed_at' => now(),
                'email_verified_at' => now(),
            ]);
            $user->load('sifConfig');

            $ok = $this->check('Alta de usuario: Veri*Factu activado', $user->hasVerifactuEnabled()) && $ok;
            $ok = $this->check('Sin certificado no emite fiscal', ! $user->canEmitFiscalInvoices()) && $ok;

            $client = Client::create([
                'user_id' => $user->id,
                'name' => 'Cliente prueba',
                'email' => 'cliente@example.test',
            ]);

            $proforma = $this->makeInvoice($user, $client, 'F-PROVE-A');
            $withoutCert = $billing->createAltaRecord($proforma);
            $ok = $this->check('Envío sin .p12 = proforma (sin registro SIF)', $withoutCert === null) && $ok;

            $user->sifConfig->update([
                'cert_path' => 'sif/certs/user_'.$user->id.'.p12.enc',
                'cert_expires_at' => now()->addYear(),
            ]);
            $user->unsetRelation('sifConfig');
            $user->load('sifConfig');
            $ok = $this->check('Con certificado puede emitir fiscal', $user->canEmitFiscalInvoices()) && $ok;

            $fiscal = $this->makeInvoice($user, $client, 'F-PROVE-B');
            $alta = $billing->createAltaRecord($fiscal);
            $ok = $this->check('Alta SIF creada', $alta instanceof BillingRecord) && $ok;
            $ok = $this->check('Hash SHA-256 de 64 caracteres', (bool) preg_match('/^[A-F0-9]{64}$/', (string) $alta?->hash_current)) && $ok;
            $ok = $this->check('Job AEAT encolado', Queue::size() > 0) && $ok;

            $encadenada = $billing->createAltaRecord($this->makeInvoice($user, $client, 'F-PROVE-C'));
            $ok = $this->check('Hash encadenado (factura 2 apunta a 1)', $encadenada?->hash_previous === $alta?->hash_current) && $ok;

            $queued = Queue::size();
            config(['demo.admin_email' => $user->email]);
            $user->sifConfig->update(['is_dev_cert' => true]);
            $user->unsetRelation('sifConfig');
            $sandbox = $billing->createAltaRecord($this->makeInvoice($user, $client, 'F-PROVE-S'));
            $ok = $this->check(
                'Sandbox acepta sin enviar a AEAT',
                $sandbox?->aeat_status === BillingRecord::STATUS_ACCEPTED
                    && ($sandbox->aeat_response['sandbox'] ?? false) === true
            ) && $ok;
            $ok = $this->check('Sandbox no encola SOAP', Queue::size() === $queued) && $ok;

            $this->newLine();
            if ($alta) {
                $this->line('Hash alta: '.$alta->hash_current);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('FALLO: '.$e->getMessage());

            return self::FAILURE;
        }

        DB::rollBack();
        $this->newLine();
        $this->line('Datos de prueba revertidos (transacción deshecha).');
        $this->line('El envío real a AEAT requiere el .p12 FNMT del autónomo; Hacienda no acepta el certificado de desarrollo.');

        if (! $ok) {
            $this->error('Veri*Factu: hay fallos en la demostración.');

            return self::FAILURE;
        }

        $this->info('Veri*Factu: demostración OK.');

        return self::SUCCESS;
    }

    private function check(string $label, bool $pass): bool
    {
        if ($pass) {
            $this->line('✓ '.$label);
        } else {
            $this->error('✗ '.$label);
        }

        return $pass;
    }

    private function makeInvoice(User $user, Client $client, string $number): Document
    {
        $invoice = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => $number,
            'status' => Document::STATUS_SENT,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => Str::random(32),
            'sent_at' => now(),
        ]);

        $invoice->lineItems()->create([
            'description' => 'Servicio demostración',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        return $invoice->fresh(['user.sifConfig', 'client', 'lineItems']);
    }
}
