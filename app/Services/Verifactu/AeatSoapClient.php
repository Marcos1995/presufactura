<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\UserSifConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AeatSoapClient
{
    public function submit(BillingRecord $record, string $certPassword): array
    {
        if (! extension_loaded('soap')) {
            return [
                'success' => false,
                'message' => 'ext-soap no disponible en este servidor',
            ];
        }

        $record->loadMissing(['user.sifConfig', 'document']);
        $sifConfig = $record->user->sifConfig;

        if (! $sifConfig?->cert_path || ! Storage::disk('local')->exists($sifConfig->cert_path)) {
            return [
                'success' => false,
                'message' => 'Certificado no configurado',
            ];
        }

        $env = config('verifactu.env', 'preprod');
        $wsdl = config("verifactu.wsdl.{$env}");

        try {
            $certData = $this->loadCertificate($sifConfig, $certPassword);
            $xml = Storage::disk('local')->get($record->xml_path);

            $client = new \SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'soap_version' => SOAP_1_1,
                'local_cert' => $certData['pem'],
                'passphrase' => $certData['passphrase'],
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            $method = $record->isAlta() ? 'RegFactuSistemaFacturacion' : 'RegFactuSistemaFacturacion';
            $response = $client->__soapCall($method, [['RegistroFactura' => $xml]]);

            Log::info('AEAT SOAP response', [
                'billing_record_id' => $record->id,
                'method' => $method,
            ]);

            return [
                'success' => true,
                'message' => 'Enviado correctamente',
                'response' => json_decode(json_encode($response), true),
            ];
        } catch (\SoapFault $e) {
            Log::warning('AEAT SOAP fault', [
                'billing_record_id' => $record->id,
                'code' => $e->faultcode ?? null,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->faultcode ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('AEAT SOAP error', [
                'billing_record_id' => $record->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            if (isset($certData['pem']) && is_file($certData['pem'])) {
                @unlink($certData['pem']);
            }
        }
    }

    /** @return array{pem: string, passphrase: string} */
    private function loadCertificate(UserSifConfig $config, string $password): array
    {
        $encrypted = Storage::disk('local')->get($config->cert_path);
        $p12Content = decrypt($encrypted);

        $certs = [];
        if (! openssl_pkcs12_read($p12Content, $certs, $password)) {
            throw new \RuntimeException('No se pudo leer el certificado .p12. Verifica la contraseña.');
        }

        $pemPath = storage_path('app/sif/certs/tmp_'.uniqid().'.pem');
        if (! is_dir(dirname($pemPath))) {
            mkdir(dirname($pemPath), 0700, true);
        }

        $pemContent = $certs['cert'].$certs['pkey'];
        file_put_contents($pemPath, $pemContent);

        return [
            'pem' => $pemPath,
            'passphrase' => $password,
        ];
    }
}
