<?php

/**
 * Catálogo orientativo de notificaciones frecuentes.
 * Las URLs son sedes oficiales; no inventar otras en runtime.
 */
return [
    'requerimiento_aeat' => [
        'label' => 'Requerimiento de la AEAT',
        'what' => 'Hacienda te pide información o documentación sobre tu actividad o declaraciones.',
        'actions' => 'Lee el plazo, reúne lo pedido y responde por la sede electrónica de la AEAT dentro de plazo.',
        'url' => 'https://sede.agenciatributaria.gob.es/',
    ],
    'comprobacion_limitada' => [
        'label' => 'Comprobación limitada',
        'what' => 'Procedimiento en el que la AEAT revisa datos concretos de tus declaraciones sin ser una inspección completa.',
        'actions' => 'Presenta alegaciones o documentación por sede electrónica antes de la fecha indicada en la carta.',
        'url' => 'https://sede.agenciatributaria.gob.es/',
    ],
    'apremio' => [
        'label' => 'Providencia de apremio',
        'what' => 'Te comunican que una deuda tributaria no pagada en periodo voluntario pasa a vía de apremio (con recargo).',
        'actions' => 'Consulta el importe y plazo en la sede; si procede, paga o solicita aplazamiento/fraccionamiento en la AEAT. Esto es informativo, no un consejo de pago.',
        'url' => 'https://sede.agenciatributaria.gob.es/',
    ],
    'embargo_creditos' => [
        'label' => 'Diligencia de embargo (créditos / terceros)',
        'what' => 'La administración ordena retener créditos que te deban terceros para cobrarse una deuda.',
        'actions' => 'Identifica el procedimiento, el órgano y el plazo de alegaciones en la propia diligencia; gestiona el trámite solo por los canales oficiales indicados. No facilitamos datos ni gestiones bancarias.',
        'url' => 'https://sede.agenciatributaria.gob.es/',
    ],
    'regularizacion_cuota_ss' => [
        'label' => 'Regularización de cuotas de la Seguridad Social',
        'what' => 'La TGSS te comunica el resultado de la regularización anual de tu cuota de autónomo (posible pago o devolución).',
        'actions' => 'Revisa el periodo, el plazo y si hay recargo; consulta Importass. Sobre el modelo 130, es orientativo: pregunta a tu asesor cómo reflejarlo.',
        'url' => 'https://portal.seg-social.gob.es/wps/portal/importass',
    ],
    'notificacion_dehu' => [
        'label' => 'Notificación por DEHú / dirección electrónica',
        'what' => 'Te avisan de que hay una notificación administrativa a tu disposición. El plazo suele contar desde la puesta a disposición, aunque no la abras.',
        'actions' => 'Entra en DEHú o Carpeta Ciudadana, abre la notificación y anota el plazo real del acto notificado.',
        'url' => 'https://dehu.redsara.es/',
    ],
    'desconocido' => [
        'label' => 'Documento no clasificado con seguridad',
        'what' => 'No hemos podido identificar el tipo con suficiente confianza.',
        'actions' => 'Lee el documento con calma, anota plazos y consulta la sede del organismo emisor o un profesional. No ignores una notificación oficial.',
        'url' => 'https://sede.agenciatributaria.gob.es/',
    ],
];
