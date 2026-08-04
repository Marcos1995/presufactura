<?php

return [
    [
        'question' => '¿PresuFactura cumple Veri*Factu?',
        'answer' => 'Sí. Con Veri*Factu activado en Configuración y un certificado electrónico válido, PresuFactura genera registros SIF con hash encadenado, código QR en el PDF y envío a la AEAT (modalidad VERI*FACTU). Sin activarlo, los documentos siguen siendo proforma.',
        'landing' => true,
    ],
    [
        'question' => '¿Qué diferencia hay entre Free y Pro?',
        'answer' => 'El plan Free incluye 3 documentos al mes (presupuestos o facturas), clientes ilimitados, PDF proforma y enlace público. Pro (12 €/mes) añade documentos ilimitados, recordatorios automáticos al cliente (+3, +7 y +14 días tras vencimiento) y un aviso por email al autónomo el día +10 para que confirmes si cobraste.',
        'landing' => true,
    ],
    [
        'question' => '¿Puedo empezar gratis?',
        'answer' => 'Sí. Regístrate sin tarjeta y usa el plan Free con 3 documentos al mes. Puedes actualizar a Pro cuando lo necesites desde la página de precios o tu panel.',
        'landing' => true,
    ],
    [
        'question' => '¿Cómo cancelo la suscripción Pro?',
        'answer' => 'Entra en Suscripción en tu panel y pulsa «Gestionar suscripción en Stripe». Desde el portal de Stripe puedes cancelar en cualquier momento. Seguirás con acceso Pro hasta el final del periodo facturado.',
        'landing' => true,
    ],
    [
        'question' => '¿Qué pasa con mis datos si cierro la cuenta?',
        'answer' => 'Tus datos (perfil, clientes y documentos) se almacenan mientras mantengas la cuenta activa. Si solicitas la baja, eliminamos tu información en un plazo máximo de 30 días, salvo obligación legal de conservación. Consulta nuestra política de privacidad para más detalle.',
        'landing' => false,
    ],
    [
        'question' => '¿Los documentos tienen validez legal?',
        'answer' => 'Con Veri*Factu desactivado, los PDF son proforma (cobro y recordatorios). Con Veri*Factu activado y certificado válido, las facturas enviadas generan registros SIF con validez fiscal ante la AEAT.',
        'landing' => false,
    ],
    [
        'question' => '¿Puedo personalizar facturas y presupuestos?',
        'answer' => 'Sí. En Configuración puedes añadir tu logo, datos fiscales, IBAN, prefijos de numeración, IVA por defecto y días de vencimiento. Los recordatorios también son configurables (plan Pro).',
        'landing' => false,
    ],
    [
        'question' => '¿Mis clientes pueden aceptar presupuestos online?',
        'answer' => 'Sí. Al enviar un presupuesto reciben un enlace público donde pueden ver el detalle y aceptarlo con un click. También puedes convertir un presupuesto aceptado en factura borrador.',
        'landing' => false,
    ],
    [
        'question' => '¿Cómo cobro más rápido con PresuFactura?',
        'answer' => 'Envía la factura por email con PDF, comparte el enlace público con tu IBAN visible y activa recordatorios Pro. Tus clientes también pueden pulsar «He pagado» para avisarte.',
        'landing' => false,
    ],
    [
        'question' => '¿Necesito instalar algo?',
        'answer' => 'No. PresuFactura funciona en el navegador. Solo necesitas conexión a internet y tu email configurado para enviar documentos a clientes.',
        'landing' => false,
    ],
];
