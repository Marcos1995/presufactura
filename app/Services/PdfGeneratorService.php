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
        $document->load(['user', 'client', 'lineItems']);

        $html = View::make('pdf.invoice', ['document' => $document])->render();

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
}
