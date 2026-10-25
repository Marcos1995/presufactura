<?php

return [
    'deadline_note' => 'Veri*Factu / SIF: obligación progresiva; muchas empresas deben adaptarse antes de 2027. Comprueba tu caso en la AEAT.',
    'aeat_url' => 'https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html',
    'questions' => [
        [
            'id' => 'emite_facturas',
            'text' => '¿Emites facturas por tu actividad económica en España?',
            'options' => ['si' => 'Sí', 'no' => 'No'],
        ],
        [
            'id' => 'soporte',
            'text' => '¿Con qué elaboras hoy las facturas?',
            'options' => [
                'word_excel' => 'Word / Excel / plantillas',
                'software' => 'Programa de facturación',
                'gestoria' => 'Las hace mi gestoría',
                'papel' => 'Papel u otro',
            ],
        ],
        [
            'id' => 'volumen',
            'text' => '¿Cuántas facturas emites al mes, aproximadamente?',
            'options' => [
                '1_5' => '1–5',
                '6_20' => '6–20',
                '20_plus' => 'Más de 20',
            ],
        ],
        [
            'id' => 'clientes',
            'text' => '¿Facturas sobre todo a empresas/autónomos o a particulares?',
            'options' => [
                'b2b' => 'Empresas / autónomos',
                'b2c' => 'Particulares',
                'mixto' => 'Mixto',
            ],
        ],
        [
            'id' => 'certificado',
            'text' => '¿Tienes certificado electrónico de persona física o de empresa (.p12 / FNMT / etc.)?',
            'options' => ['si' => 'Sí', 'no' => 'No', 'nose' => 'No lo sé'],
        ],
        [
            'id' => 'sif_actual',
            'text' => '¿Tu sistema actual genera registro verificable / hash / QR Veri*Factu?',
            'options' => ['si' => 'Sí', 'no' => 'No', 'nose' => 'No lo sé'],
        ],
        [
            'id' => 'plazo',
            'text' => '¿Quieres estar listo antes de 2027?',
            'options' => ['si' => 'Sí', 'mas_adelante' => 'Más adelante'],
        ],
        [
            'id' => 'exencion_crees',
            'text' => '¿Crees estar en algún régimen especial o exento de Veri*Factu?',
            'options' => ['si' => 'Sí / creo que sí', 'no' => 'No', 'nose' => 'No lo sé'],
        ],
    ],
    'checklist_fields' => [
        'issuer_name' => 'Nombre o razón social del emisor',
        'issuer_tax_id' => 'NIF/CIF del emisor',
        'receiver_name' => 'Nombre o razón social del destinatario',
        'receiver_tax_id' => 'NIF/CIF del destinatario (si aplica)',
        'invoice_number' => 'Número de factura',
        'issue_date' => 'Fecha de expedición',
        'description' => 'Descripción de operaciones',
        'tax_base' => 'Base imponible',
        'vat_rate' => 'Tipo impositivo (IVA u otro)',
        'vat_amount' => 'Cuota tributaria',
        'total' => 'Importe total',
    ],
];
