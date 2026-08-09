<?php

return [
    [
        'question' => '¿PresuFactura cumple Veri*Factu?',
        'answer' => 'Sí. Con Veri*Factu activado en Configuración y un certificado electrónico válido, PresuFactura genera registros SIF con hash encadenado, código QR en el PDF y envío a la AEAT (modalidad VERI*FACTU). Sin activarlo, los documentos siguen siendo proforma.',
        'landing' => true,
    ],
    [
        'question' => '¿Cuánto cuesta PresuFactura?',
        'answer' => 'Nada. PresuFactura es completamente gratis: documentos ilimitados, clientes ilimitados, recordatorios automáticos, botón «He pagado» y Veri*Factu opcional. No hay planes de pago ni tarjeta.',
        'landing' => true,
    ],
    [
        'question' => '¿Puedo empezar sin tarjeta?',
        'answer' => 'Sí. Regístrate sin tarjeta y tienes acceso inmediato a todas las funciones.',
        'landing' => true,
    ],
    [
        'question' => '¿Hay límites de documentos?',
        'answer' => 'No. Puedes crear tantos presupuestos y facturas como necesites.',
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
        'answer' => 'Sí. En Configuración puedes añadir tu logo, datos fiscales, IBAN, prefijos de numeración, IVA por defecto, días de vencimiento y recordatorios automáticos.',
        'landing' => false,
    ],
    [
        'question' => '¿Mis clientes pueden aceptar presupuestos online?',
        'answer' => 'Sí. Al enviar un presupuesto reciben un enlace público donde pueden ver el detalle y aceptarlo con un click. También puedes convertir un presupuesto aceptado en factura borrador.',
        'landing' => false,
    ],
    [
        'question' => '¿Cómo cobro más rápido con PresuFactura?',
        'answer' => 'Envía la factura por email con PDF, comparte el enlace público con tu IBAN visible y activa recordatorios. Tus clientes también pueden pulsar «He pagado» para avisarte.',
        'landing' => false,
    ],
    [
        'question' => '¿Necesito instalar algo?',
        'answer' => 'No. PresuFactura funciona en el navegador. Solo necesitas conexión a internet y tu email configurado para enviar documentos a clientes.',
        'landing' => false,
    ],
];
