@include('pdf._document', [
    'document' => $document,
    'logoDataUri' => $logoDataUri ?? null,
    'docTitle' => ($isFiscal ?? false) ? 'FACTURA' : 'FACTURA PROFORMA',
    'proformaBadge' => ($isFiscal ?? false) ? 'Factura verificable' : 'No válida como factura fiscal',
    'isFiscal' => $isFiscal ?? false,
    'qrDataUri' => $qrDataUri ?? null,
    'showIban' => true,
    'metaExtra' => $document->due_date ? [
        'label' => 'Vencimiento',
        'value' => $document->due_date->format('d/m/Y'),
    ] : null,
])
