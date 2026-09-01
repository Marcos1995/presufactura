<?php

namespace App\Console\Commands;

use App\Support\VerifactuProductionCheck;
use Illuminate\Console\Command;

class VerifactuCheckCommand extends Command
{
    protected $signature = 'presufactura:verifactu-check';

    protected $description = 'Comprueba el sandbox Veri*Factu en este servidor (sin enviar nada a AEAT)';

    public function handle(): int
    {
        $report = VerifactuProductionCheck::run();

        foreach ($report['checks'] as $check) {
            if ($check['pass']) {
                $this->line('✓ '.$check['label']);
            } else {
                $this->error('✗ '.$check['label']);
                if (filled($check['hint'])) {
                    $this->line('  → '.$check['hint']);
                }
            }
        }

        $this->newLine();
        $this->line('Hacienda real no se puede probar con el .p12 de desarrollo: hace falta tu certificado FNMT.');

        if (! $report['ok']) {
            $this->error('Veri*Factu pruebas: FALLIDO.');

            return self::FAILURE;
        }

        $this->info('Veri*Factu pruebas: OK. En el panel abre la factura DEMO-F-FIS (Aceptada (pruebas)).');

        if (! app()->environment('testing')) {
            $qr = app(\App\Services\Verifactu\QrService::class);
            foreach ($qr->probeOfficialCotejoBoth() as $probe) {
                if ($probe['ok']) {
                    $this->line('✓ Cotejo AEAT '.$probe['env'].': ejemplo oficial '.$probe['mensaje']);
                } else {
                    $this->warn('⚠ Cotejo AEAT '.$probe['env'].': '.$probe['mensaje']);
                }
            }
            foreach (['preprod', 'prod'] as $env) {
                $wsdl = $qr->probeOfficialWsdl($env);
                if ($wsdl['ok']) {
                    $this->line('✓ WSDL AEAT '.$env.': '.$wsdl['mensaje']);
                } else {
                    $this->warn('⚠ WSDL AEAT '.$env.': '.$wsdl['mensaje']);
                }
            }
        }

        return self::SUCCESS;
    }
}
