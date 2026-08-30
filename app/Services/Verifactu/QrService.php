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

    public function buildUrl(BillingRecord $record): string
    {
        $record->loadMissing(['document.user.sifConfig']);
        $document = $record->document;
        $user = $document->user;
        $sifConfig = $user->sifConfig;

        $env = config('verifactu.env', 'preprod');
        $mode = $sifConfig?->mode ?? config('verifactu.mode', 'verifactu');
        $baseUrl = (string) config("verifactu.qr_urls.{$env}.{$mode}");

        $nif = strtoupper((string) preg_replace('/[\s-]+/', '', (string) $user->tax_id));

        $params = http_build_query([
            'nif' => $nif,
            'numserie' => (string) $document->number,
            'fecha' => $document->issue_date->format('d-m-Y'),
            'importe' => number_format((float) $document->total, 2, '.', ''),
        ], '', '&', PHP_QUERY_RFC3986);

        return $baseUrl.'?'.$params;
    }

    public function generateDataUri(BillingRecord $record): string
    {
        $url = $this->buildUrl($record);

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
        $record = $document->relationLoaded('billingRecord')
            ? $document->billingRecord
            : $document->billingRecord()->first();

        if (! $record || ! $this->shouldShowQr($record)) {
            return null;
        }

        return [
            'url' => $this->buildUrl($record),
            'dataUri' => $this->generateDataUri($record),
        ];
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
     * Cotejo real AEAT del ejemplo oficial (especificación QR). El 5.º parámetro
     * formato=json no va nunca en el QR; solo en esta sonda HTTP.
     *
     * @return array{ok: bool, mensaje: string, env: string}
     */
    public function probeOfficialCotejo(): array
    {
        $env = config('verifactu.env', 'preprod');
        $baseUrl = (string) config("verifactu.qr_urls.{$env}.verifactu");
        $url = $baseUrl.'?'.http_build_query([
            'nif' => '89890001K',
            'numserie' => '12345678-G33',
            'fecha' => '01-09-2024',
            'importe' => '241.4',
            'formato' => 'json',
        ], '', '&', PHP_QUERY_RFC3986);

        try {
            $response = Http::timeout(10)->acceptJson()->get($url);
            $json = $response->json();
            $ok = $response->successful()
                && is_array($json)
                && ($json['status'] ?? '') === 'OK';

            return [
                'ok' => $ok,
                'mensaje' => is_array($json) ? (string) ($json['mensaje'] ?? $response->status()) : (string) $response->status(),
                'env' => (string) $env,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'mensaje' => $e->getMessage(),
                'env' => (string) $env,
            ];
        }
    }
}
