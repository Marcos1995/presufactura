<?php

namespace App\Services;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Services\Verifactu\QrService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class PdfGeneratorService
{
    public function __construct(
        private QrService $qrService,
    ) {}

    public function generateInvoicePdf(Document $document): string
    {
        $document->load(['user', 'client', 'lineItems', 'billingRecord']);

        $billingRecord = $document->billingRecord;
        $qrDataUri = null;
        $isFiscal = false;

        if ($billingRecord && $this->qrService->shouldShowQr($billingRecord)) {
            $qrDataUri = $this->qrService->generateDataUri($billingRecord);
            $isFiscal = true;
        }

        return $this->renderPdf('pdf.invoice', $document, [
            'qrDataUri' => $qrDataUri,
            'isFiscal' => $isFiscal,
        ]);
    }

    public function generateQuotePdf(Document $document): string
    {
        return $this->renderPdf('pdf.quote', $document);
    }

    /** @param  array<string, mixed>  $extra */
    private function renderPdf(string $view, Document $document, array $extra = []): string
    {
        $document->load(['user', 'client', 'lineItems']);

        $html = View::make($view, array_merge([
            'document' => $document,
            'logoDataUri' => $this->logoDataUri($document->user->logo_path),
        ], $extra))->render();

        $tempDir = storage_path('framework/cache/dompdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('tempDir', $tempDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function logoDataUri(?string $logoPath): ?string
    {
        if (! $logoPath) {
            return null;
        }

        $fullPath = storage_path('app/public/'.$logoPath);
        if (! is_file($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($fullPath));
    }
}
