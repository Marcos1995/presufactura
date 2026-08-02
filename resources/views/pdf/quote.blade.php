@include('pdf._document', [
    'document' => $document,
    'logoDataUri' => $logoDataUri ?? null,
    'docTitle' => 'PRESUPUESTO',
    'proformaBadge' => 'No válido como documento fiscal',
    'showIban' => false,
    'metaExtra' => $document->valid_until ? [
        'label' => 'Válido hasta',
        'value' => $document->valid_until->format('d/m/Y'),
    ] : null,
])
