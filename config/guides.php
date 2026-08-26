<?php

return [
    'como-hacer-factura-autonomo' => [
        'title' => 'Cómo hacer una factura siendo autónomo',
        'heading' => 'Cómo hacer una factura siendo autónomo',
        'meta' => 'Guía práctica para emitir una factura como autónomo en España: datos fiscales, IVA, numeración y envío. Prueba PresuFactura gratis.',
        'lead' => 'Una factura válida necesita tus datos, los del cliente, una numeración correlativa, bases e IVA y una forma de envío. Con Veri*Factu, además, el registro debe ser verificable ante Hacienda.',
        'sections' => [
            [
                'heading' => 'Qué debe incluir una factura',
                'paragraphs' => [
                    'Número y fecha, tus datos fiscales (nombre o razón social, NIF y domicilio), los del cliente, descripción del servicio o producto, base imponible, tipo de IVA y total.',
                    'Si estás en recargo de equivalencia o en una operación exenta, indícalo. El IBAN facilita el cobro; no sustituye los datos fiscales.',
                ],
            ],
            [
                'heading' => 'Numeración correlativa',
                'paragraphs' => [
                    'Las series deben ser correlativas dentro del ejercicio. No reutilices números ni dejes huecos a propósito. En PresuFactura el prefijo y el contador se asignan al crear el documento.',
                ],
            ],
            [
                'heading' => 'Proforma o factura fiscal',
                'paragraphs' => [
                    'Sin Veri*Factu, PresuFactura emite documentos proforma con un aviso claro: no tienen validez fiscal. Cuando actives Veri*Factu y subas tu certificado .p12, las facturas enviadas generan registro SIF, QR y remisión a la AEAT.',
                    'No hace falta configurar Hacienda para crear tu primer presupuesto o una factura de prueba.',
                ],
            ],
        ],
        'cta' => 'Crea tu primera factura en minutos',
    ],
    'plantilla-presupuesto-autonomos' => [
        'title' => 'Plantilla de presupuesto para autónomos',
        'heading' => 'Plantilla de presupuesto para autónomos',
        'meta' => 'Estructura de un presupuesto profesional para autónomos: líneas, IVA, validez y aceptación online. Usa PresuFactura sin Excel.',
        'lead' => 'Un presupuesto claro cierra más trabajos. Incluye alcance, precios, IVA, fecha de validez y una forma sencilla de aceptar.',
        'sections' => [
            [
                'heading' => 'Estructura recomendada',
                'paragraphs' => [
                    'Cabecera con tus datos, cliente, número de presupuesto y fecha. Líneas con descripción, cantidad, precio e IVA. Totales visibles. Notas de validez, forma de pago y qué no está incluido.',
                ],
            ],
            [
                'heading' => 'Aceptación sin imprimir',
                'paragraphs' => [
                    'Envía el PDF por email y un enlace público. El cliente puede aceptar online. Después conviertes el presupuesto en factura borrador sin volver a teclear las líneas.',
                ],
            ],
            [
                'heading' => 'De presupuesto a cobro',
                'paragraphs' => [
                    'Cuando el trabajo está aceptado, genera la factura, el PDF y el recordatorio de cobro. El mismo cliente y las mismas líneas; menos errores y menos Excel.',
                ],
            ],
        ],
        'cta' => 'Prueba un presupuesto de ejemplo',
    ],
    'verifactu-para-autonomos' => [
        'title' => 'Veri*Factu para autónomos',
        'heading' => 'Veri*Factu para autónomos, explicado sin jerga innecesaria',
        'meta' => 'Qué es Veri*Factu para autónomos en España: certificado .p12, hash encadenado, QR y envío a la AEAT. Actívalo en PresuFactura cuando quieras.',
        'lead' => 'Veri*Factu (RRSIF) permite emitir facturas verificables ante Hacienda. En PresuFactura es opcional: primero entiende el producto; después activa el envío a la AEAT.',
        'sections' => [
            [
                'heading' => 'Qué implica',
                'paragraphs' => [
                    'Cada factura enviada genera un registro SIF con hash encadenado, un código QR en el PDF y remisión SOAP a la AEAT. Las anulaciones y rectificativas quedan ligadas a ese registro.',
                ],
            ],
            [
                'heading' => 'Qué necesitas',
                'paragraphs' => [
                    'Un certificado electrónico .p12 del autónomo y su contraseña. PresuFactura lo guarda cifrado y no lo muestra en pantalla, logs ni exportaciones públicas.',
                    'Hasta que no lo actives, los documentos son proforma. Puedes crear presupuestos, PDFs y emails igualmente.',
                ],
            ],
            [
                'heading' => 'Cuándo activarlo',
                'paragraphs' => [
                    'Cuando vayas a emitir facturas con validez fiscal. No es un requisito para registrarte ni para probar el flujo presupuesto → factura → PDF.',
                ],
            ],
        ],
        'cta' => 'Crea cuenta y actívalo más tarde',
    ],
    'como-reclamar-factura-pendiente' => [
        'title' => 'Cómo reclamar una factura pendiente',
        'heading' => 'Cómo reclamar una factura pendiente',
        'meta' => 'Cómo recordar el cobro de una factura vencida siendo autónomo: plazos, tono y automatización. Recordatorios incluidos en PresuFactura.',
        'lead' => 'Cobrar a tiempo empieza por un vencimiento claro, un PDF enviado y un recordatorio educado. Reclamar no tiene que ser improvisar un email cada viernes.',
        'sections' => [
            [
                'heading' => 'Antes del vencimiento',
                'paragraphs' => [
                    'Indica la fecha de vencimiento y el IBAN en la factura y en el enlace público. El cliente debe poder pagar sin pedirte los datos otra vez.',
                ],
            ],
            [
                'heading' => 'Después del vencimiento',
                'paragraphs' => [
                    'Un primer aviso a los pocos días, otro a la semana y un tercero a las dos semanas suele bastar. Mantén un tono profesional y adjunta de nuevo el PDF o el enlace.',
                    'PresuFactura envía recordatorios automáticos al cliente (por ejemplo +3, +7 y +14 días) y un aviso al autónomo para preguntar si ya cobró. El cliente puede pulsar «He pagado» en el enlace público.',
                ],
            ],
            [
                'heading' => 'Si no pagan',
                'paragraphs' => [
                    'Documenta envíos y fechas. Valora una rectificativa o anulación solo si procede fiscalmente. Un software no sustituye el criterio de tu asesor, pero sí evita que se te olvide reclamar.',
                ],
            ],
        ],
        'cta' => 'Activa recordatorios al registrarte',
    ],
    'presupuestos-facturas-sin-excel' => [
        'title' => 'Cómo hacer presupuestos y facturas sin Excel',
        'heading' => 'Cómo hacer presupuestos y facturas sin Excel',
        'meta' => 'Deja de copiar plantillas de Excel: numeración, IVA, PDF y email en un solo flujo para autónomos. PresuFactura es gratis.',
        'lead' => 'Excel vale para un cálculo. Se queda corto cuando hay clientes, numeración, PDFs, emails y recordatorios. Un flujo único evita copiar mal el IVA o el número de factura.',
        'sections' => [
            [
                'heading' => 'El problema de la plantilla',
                'paragraphs' => [
                    'Cada archivo es una copia. Fácil repetir un número, olvidar el vencimiento o enviar un PDF desactualizado. El histórico queda en carpetas, no en un panel.',
                ],
            ],
            [
                'heading' => 'Un solo flujo',
                'paragraphs' => [
                    'Regístrate, crea un presupuesto de prueba, conviértelo en factura, descarga el PDF y envíalo por email. Veri*Factu se configura después, cuando quieras facturas fiscales.',
                ],
            ],
            [
                'heading' => 'Qué ganas',
                'paragraphs' => [
                    'Clientes reutilizables, IVA calculado, numeración correlativa, enlace público y recordatorios. Sin tarjeta y sin límites de documentos.',
                ],
            ],
        ],
        'cta' => 'Haz tu primer documento sin Excel',
    ],
];
