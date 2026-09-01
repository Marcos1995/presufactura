<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\Document;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Http;

class QrService
{
    /** Orden HAC/1177/2024 art. 20.1.b — texto visible junto al QR. */
    public const LEGEND = 'Factura verificable en la sede electrónica de la AEAT';

    /**
     * Ejemplo oficial AEAT (Detalle especificaciones técnicas del código QR).
     * El cotejo de esta factura de prueba responde OK en preprod y en producción.
     *
     * @var array{nif: string, numserie: string, fecha: string, importe: string}
     */
    public const OFFICIAL_EXAMPLE = [
        'nif' => '89890001K',
        'numserie' => '12345678-G33',
        'fecha' => '01-09-2024',
        'importe' => '241.4',
    ];

    public function buildUrl(BillingRecord $record): string
    {
        $record->loadMissing(['document.user.sifConfig']);

        return $this->buildUrlForDocument($record->document);
    }

    public function buildUrlForDocument(Document $document): string
    {
        $document->loadMissing('user.sifConfig');
        $user = $document->user;
        $sifConfig = $user->sifConfig;

        $env = config('verifactu.env', 'preprod');
        $mode = $sifConfig?->mode ?? config('verifactu.mode', 'verifactu');

        return $this->cotejoUrl($env, $mode, [
            'nif' => strtoupper((string) preg_replace('/[\s-]+/', '', (string) $user->tax_id)),
            'numserie' => (string) $document->number,
            'fecha' => $document->issue_date->format('d-m-Y'),
            'importe' => number_format((float) $document->total, 2, '.', ''),
        ]);
    }

    /**
     * @param  array{nif: string, numserie: string, fecha: string, importe: string}  $params
     */
    public function cotejoUrl(string $env, string $mode, array $params, bool $json = false): string
    {
        $baseUrl = (string) config("verifactu.qr_urls.{$env}.{$mode}");
        $query = $params;
        if ($json) {
            $query['formato'] = 'json';
        }

        return $baseUrl.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    public function generateDataUri(BillingRecord $record): string
    {
        return $this->pngDataUri($this->buildUrl($record));
    }

    public function pngDataUri(string $url): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 400,
            margin: 24,
        ))->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }

    public function payloadForDocument(Document $document): ?array
    {
        if (! $this->shouldShowQrForDocument($document)) {
            return null;
        }

        $url = $this->buildUrlForDocument($document);

        return [
            'url' => $url,
            'dataUri' => $this->pngDataUri($url),
        ];
    }

    public function shouldShowQrForDocument(Document $document): bool
    {
        if (! $document->isInvoice()) {
            return false;
        }

        if (! in_array($document->status, [
            Document::STATUS_SENT,
            Document::STATUS_PAID,
            Document::STATUS_EXPIRED,
            Document::STATUS_PAYMENT_PENDING,
        ], true)) {
            return false;
        }

        $document->loadMissing(['user.sifConfig', 'billingRecord']);

        if ($document->user->hasVerifactuEnabled()) {
            return true;
        }

        $record = $document->billingRecord;

        return $record !== null && $this->shouldShowQr($record);
    }

    public function shouldShowQr(BillingRecord $record): bool
    {
        $record->loadMissing('document');

        return $record->isAlta()
            && in_array($record->document->status, [
                Document::STATUS_SENT,
                Document::STATUS_PAID,
                Document::STATUS_EXPIRED,
                Document::STATUS_PAYMENT_PENDING,
            ], true);
    }

    /**
     * Cotejo real AEAT del ejemplo oficial. formato=json no va nunca en el QR.
     *
     * @return array{ok: bool, mensaje: string, env: string}
     */
    public function probeOfficialCotejo(?string $env = null): array
    {
        $env = $env ?: (string) config('verifactu.env', 'preprod');
        $url = $this->cotejoUrl($env, 'verifactu', self::OFFICIAL_EXAMPLE, json: true);

        try {
            $response = Http::timeout(10)->acceptJson()->get($url);
            $json = $response->json();
            $ok = $response->successful()
                && is_array($json)
                && ($json['status'] ?? '') === 'OK';

            return [
                'ok' => $ok,
                'mensaje' => is_array($json) ? (string) ($json['mensaje'] ?? $response->status()) : (string) $response->status(),
                'env' => $env,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'mensaje' => $e->getMessage(),
                'env' => $env,
            ];
        }
    }

    /**
     * @return array{preprod: array{ok: bool, mensaje: string, env: string}, prod: array{ok: bool, mensaje: string, env: string}}
     */
    public function probeOfficialCotejoBoth(): array
    {
        return [
            'preprod' => $this->probeOfficialCotejo('preprod'),
            'prod' => $this->probeOfficialCotejo('prod'),
        ];
    }

    /**
     * @return array{ok: bool, mensaje: string, env: string}
     */
    public function probeOfficialWsdl(string $env): array
    {
        $url = (string) config("verifactu.wsdl.{$env}");

        try {
            $response = Http::timeout(15)->get($url);
            $ok = $response->successful() && str_contains($response->body(), 'SistemaFacturacion');

            return [
                'ok' => $ok,
                'mensaje' => $ok ? 'WSDL '.$response->status() : 'HTTP '.$response->status(),
                'env' => $env,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'mensaje' => $e->getMessage(),
                'env' => $env,
            ];
        }
    }
}
