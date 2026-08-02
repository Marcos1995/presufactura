@include('pdf._document', [
    'document' => $document,
    'logoDataUri' => $logoDataUri ?? null,
    'docTitle' => 'FACTURA PROFORMA',
    'proformaBadge' => 'No válida como factura fiscal',
    'showIban' => true,
    'metaExtra' => $document->due_date ? [
        'label' => 'Vencimiento',
        'value' => $document->due_date->format('d/m/Y'),
    ] : null,
])
