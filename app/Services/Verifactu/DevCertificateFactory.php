<?php

namespace App\Services\Verifactu;

class DevCertificateFactory
{
    public const PASSWORD = 'presufactura-dev';

    public static function fixturePath(): string
    {
        return database_path('certificates/presufactura-dev.p12');
    }

    public static function makePkcs12(string $password = self::PASSWORD, int $daysValid = 365): string
    {
        try {
            return self::generateWithOpenSsl($password, $daysValid);
        } catch (\Throwable) {
            $path = self::fixturePath();
            if (is_file($path)) {
                $p12 = file_get_contents($path);
                if ($p12 !== false && $p12 !== '') {
                    return $p12;
                }
            }

            throw new \RuntimeException('No se pudo generar ni leer el certificado de desarrollo.');
        }
    }

    private static function generateWithOpenSsl(string $password, int $daysValid): string
    {
        while (openssl_error_string() !== false) {
        }

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($key === false) {
            throw new \RuntimeException('openssl_pkey_new');
        }

        $csr = openssl_csr_new(['commonName' => 'PresuFactura DEV'], $key);
        if ($csr === false) {
            throw new \RuntimeException('openssl_csr_new');
        }

        $cert = openssl_csr_sign($csr, null, $key, $daysValid);
        if ($cert === false) {
            throw new \RuntimeException('openssl_csr_sign');
        }

        $p12 = '';
        if (! openssl_pkcs12_export($cert, $p12, $key, $password) || $p12 === '') {
            throw new \RuntimeException('openssl_pkcs12_export');
        }

        return $p12;
    }
}
