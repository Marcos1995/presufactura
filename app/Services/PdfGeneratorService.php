<?php

namespace App\Services;

use App\Models\Document;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class PdfGeneratorService
{
    public function generateInvoicePdf(Document $document): string
    {
        return $this->renderPdf('pdf.invoice', $document);
    }

    public function generateQuotePdf(Document $document): string
    {
        return $this->renderPdf('pdf.quote', $document);
    }

    private function renderPdf(string $view, Document $document): string
    {
        $document->load(['user', 'client', 'lineItems']);

        $html = View::make($view, [
            'document' => $document,
            'logoDataUri' => $this->logoDataUri($document->user->logo_path),
        ])->render();

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
