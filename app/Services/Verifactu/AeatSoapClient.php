<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\UserSifConfig;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AeatSoapClient
{
    private const NS_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    private const NS_SUM = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd';

    private const NS_SUM1 = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd';

    /** @param \Closure(string, array<string, mixed>): \SoapClient|null $soapClientFactory */
    public function __construct(
        private ?\Closure $soapClientFactory = null,
    ) {}

    public function submit(BillingRecord $record, string $certPassword): array
    {
        if (! extension_loaded('soap')) {
            return $this->failure('ext-soap no disponible en este servidor', permanent: true);
        }

        $record->loadMissing(['user.sifConfig', 'document']);
        $sifConfig = $record->user->sifConfig;

        if (! $sifConfig?->cert_path || ! Storage::disk('local')->exists($sifConfig->cert_path)) {
            return $this->failure('Certificado no configurado', permanent: true);
        }

        $env = config('verifactu.env', 'preprod');
        $wsdl = config("verifactu.wsdl.{$env}");

        $certData = null;

        try {
            $certData = $this->loadCertificate($sifConfig, $certPassword);
            $registroXml = Storage::disk('local')->get($record->xml_path);
            $envelope = $this->buildSoapEnvelope($record, $registroXml);

            $client = $this->createSoapClient($wsdl, $certData);
            $responseXml = $client->__doRequest(
                $envelope,
                $client->__getLocation(),
                'RegFactuSistemaFacturacion',
                SOAP_1_1,
            );

            $parsed = $this->parseResponse($responseXml);

            Log::info('AEAT SOAP response', [
                'billing_record_id' => $record->id,
                'success' => $parsed['success'],
            ]);

            return $parsed;
        } catch (\SoapFault $e) {
            Log::warning('AEAT SOAP fault', [
                'billing_record_id' => $record->id,
                'code' => $e->faultcode ?? null,
                'message' => $e->getMessage(),
            ]);

            return $this->failure($e->getMessage(), code: $e->faultcode ?? null);
        } catch (\RuntimeException $e) {
            Log::warning('AEAT SOAP client error', [
                'billing_record_id' => $record->id,
                'message' => $e->getMessage(),
            ]);

            return $this->failure($e->getMessage(), permanent: true);
        } catch (\Throwable $e) {
            Log::error('AEAT SOAP error', [
                'billing_record_id' => $record->id,
                'message' => $e->getMessage(),
            ]);

            return $this->failure($e->getMessage());
        } finally {
            if ($certData !== null && is_file($certData['pem'])) {
                @unlink($certData['pem']);
            }
        }
    }

    public function buildSoapEnvelope(BillingRecord $record, string $registroXml): string
    {
        $record->loadMissing('user');
        $user = $record->user;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        $envelope = $dom->createElementNS(self::NS_SOAP, 'soapenv:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenv', self::NS_SOAP);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sum', self::NS_SUM);
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sum1', self::NS_SUM1);
        $dom->appendChild($envelope);

        $envelope->appendChild($dom->createElementNS(self::NS_SOAP, 'soapenv:Header'));

        $body = $dom->createElementNS(self::NS_SOAP, 'soapenv:Body');
        $envelope->appendChild($body);

        $regFactu = $dom->createElementNS(self::NS_SUM, 'sum:RegFactuSistemaFacturacion');
        $body->appendChild($regFactu);

        $cabecera = $dom->createElementNS(self::NS_SUM, 'sum:Cabecera');
        $regFactu->appendChild($cabecera);

        $obligado = $dom->createElementNS(self::NS_SUM1, 'sum1:ObligadoEmision');
        $obligado->appendChild($dom->createElementNS(
            self::NS_SUM1,
            'sum1:NombreRazon',
            htmlspecialchars($user->business_name ?: $user->name, ENT_XML1),
        ));
        $obligado->appendChild($dom->createElementNS(
            self::NS_SUM1,
            'sum1:NIF',
            strtoupper(preg_replace('/\s+/', '', (string) $user->tax_id)),
        ));
        $cabecera->appendChild($obligado);

        $registroFactura = $dom->createElementNS(self::NS_SUM, 'sum:RegistroFactura');
        $regFactu->appendChild($registroFactura);

        $registroDom = new DOMDocument('1.0', 'UTF-8');
        $registroDom->loadXML($registroXml);
        $registroFactura->appendChild($dom->importNode($registroDom->documentElement, true));

        return $dom->saveXML() ?: '';
    }

    /** @param array{pem: string, passphrase: string} $certData */
    private function createSoapClient(string $wsdl, array $certData): \SoapClient
    {
        if ($this->soapClientFactory !== null) {
            $client = ($this->soapClientFactory)($wsdl, $certData);
            if ($client instanceof \SoapClient) {
                return $client;
            }
        }

        return new \SoapClient($wsdl, [
            'trace' => true,
            'exceptions' => true,
            'soap_version' => SOAP_1_1,
            'local_cert' => $certData['pem'],
            'passphrase' => $certData['passphrase'],
            'connection_timeout' => 30,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    /** @return array{success: bool, message: string, permanent?: bool, code?: string|null, csv?: string, response?: mixed} */
    private function parseResponse(string $responseXml): array
    {
        if ($responseXml === '') {
            return $this->failure('Respuesta vacía de AEAT');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        if (@$dom->loadXML($responseXml) === false) {
            return $this->failure('Respuesta AEAT no válida');
        }

        $xpath = new DOMXPath($dom);
        $estado = trim($xpath->evaluate('string(//*[local-name()="EstadoEnvio"])'));
        $csv = trim($xpath->evaluate('string(//*[local-name()="CSV"])'));
        $errorCode = trim($xpath->evaluate('string(//*[local-name()="CodigoErrorRegistro"])'));
        $errorDesc = trim($xpath->evaluate('string(//*[local-name()="DescripcionErrorRegistro"])'));

        if ($estado !== '' && stripos($estado, 'Correcto') !== false) {
            return [
                'success' => true,
                'message' => 'Enviado correctamente',
                'csv' => $csv !== '' ? $csv : null,
                'response' => $responseXml,
            ];
        }

        if ($csv !== '') {
            return [
                'success' => true,
                'message' => 'Enviado correctamente',
                'csv' => $csv,
                'response' => $responseXml,
            ];
        }

        $message = $errorDesc !== '' ? $errorDesc : ($estado !== '' ? $estado : 'Envío rechazado por AEAT');

        return $this->failure($message, code: $errorCode !== '' ? $errorCode : null, response: $responseXml);
    }

    /** @return array{success: false, message: string, permanent?: bool, code?: string|null, response?: mixed} */
    private function failure(string $message, bool $permanent = false, ?string $code = null, mixed $response = null): array
    {
        $result = [
            'success' => false,
            'message' => $message,
        ];

        if ($permanent) {
            $result['permanent'] = true;
        }

        if ($code !== null) {
            $result['code'] = $code;
        }

        if ($response !== null) {
            $result['response'] = $response;
        }

        return $result;
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

        file_put_contents($pemPath, $certs['cert'].$certs['pkey']);

        return [
            'pem' => $pemPath,
            'passphrase' => $password,
        ];
    }
}
