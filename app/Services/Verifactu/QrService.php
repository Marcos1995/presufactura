<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\UserSifConfig;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QrService
{
    public function buildUrl(BillingRecord $record): string
    {
        $record->loadMissing(['document.user']);
        $document = $record->document;
        $user = $document->user;
        $sifConfig = $user->sifConfig;

        $env = config('verifactu.env', 'preprod');
        $mode = $sifConfig?->mode ?? config('verifactu.mode', 'verifactu');
        $baseUrl = config("verifactu.qr_urls.{$env}.{$mode}");

        $params = http_build_query([
            'nif' => strtoupper(preg_replace('/\s+/', '', $user->tax_id)),
            'numserie' => $document->number,
            'fecha' => $document->issue_date->format('d-m-Y'),
            'importe' => number_format((float) $document->total, 2, '.', ''),
        ]);

        return $baseUrl.'?'.$params;
    }

    public function generateDataUri(BillingRecord $record): string
    {
        $url = $this->buildUrl($record);

        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(200)
            ->margin(4)
            ->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }

    public function shouldShowQr(BillingRecord $record): bool
    {
        return $record->isAlta()
            && in_array($record->document->status, [
                \App\Models\Document::STATUS_SENT,
                \App\Models\Document::STATUS_PAID,
                \App\Models\Document::STATUS_EXPIRED,
                \App\Models\Document::STATUS_PAYMENT_PENDING,
            ], true);
    }
}
