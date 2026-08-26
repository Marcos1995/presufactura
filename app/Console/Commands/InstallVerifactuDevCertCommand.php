<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSifConfig;
use App\Services\Verifactu\DevCertificateFactory;
use App\Support\VerifactuSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallVerifactuDevCertCommand extends Command
{
    protected $signature = 'presufactura:verifactu-dev-cert';

    protected $description = 'Instala un .p12 de desarrollo (autofirmado) solo para DEMO_ADMIN_EMAIL. No es válido ante la AEAT.';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        VerifactuSchema::ensureDevCertColumn();

        if (! VerifactuSchema::hasSifConfigTable()) {
            $this->error('Faltan tablas Veri*Factu. Ejecuta php artisan migrate --force.');

            return self::FAILURE;
        }

        $email = config('demo.admin_email');
        if (! filled($email)) {
            $this->error('Define DEMO_ADMIN_EMAIL.');

            return self::FAILURE;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user?->isDemoAdmin()) {
            $this->error('No hay usuario demo con ese email. Crea la cuenta y completa el onboarding.');

            return self::FAILURE;
        }

        $user->ensureDefaultVerifactu();
        $config = $user->sifConfig;
        if (! $config) {
            $this->error('No se pudo crear user_sif_config.');

            return self::FAILURE;
        }

        $p12 = DevCertificateFactory::makePkcs12();
        $path = 'sif/certs/user_'.$user->id.'.p12.enc';
        Storage::disk('local')->put($path, encrypt($p12));

        $config->update([
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
            'cert_path' => $path,
            'cert_expires_at' => now()->addYear(),
            'is_dev_cert' => true,
        ]);
        $config->storeCertPassword(DevCertificateFactory::PASSWORD);

        $this->info('Certificado de desarrollo instalado para '.$user->email);
        $this->line('Sirve para XML, hash, QR y el panel. Hacienda rechaza este .p12: no existe un certificado público de AEAT.');
        $this->line('Para preprod real hace falta tu certificado electrónico FNMT de pruebas.');

        return self::SUCCESS;
    }
}
