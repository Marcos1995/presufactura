<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrepareTestUserCommand extends Command
{
    protected $signature = 'presufactura:prepare-test-user';

    protected $description = 'Deja a DEMO_ADMIN_EMAIL en sandbox de pruebas con certificado, facturas y Veri*Factu aceptado';

    public function handle(): int
    {
        $email = config('demo.admin_email');
        if (! filled($email)) {
            $this->error('Define DEMO_ADMIN_EMAIL.');

            return self::FAILURE;
        }

        $cert = $this->call('presufactura:verifactu-dev-cert');
        if ($cert !== self::SUCCESS) {
            return $cert;
        }

        $seed = $this->call('presufactura:seed-demo');
        if ($seed !== self::SUCCESS) {
            return $seed;
        }

        $this->info('Usuario de pruebas listo: '.$email);
        $this->line('Entra en el panel: facturas DEMO-F-FIS (fiscal sandbox) y DEMO-F-PRO (proforma).');
        $this->line('Hacienda real no recibe nada hasta que subas un .p12 FNMT.');

        return self::SUCCESS;
    }
}
