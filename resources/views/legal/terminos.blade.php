@extends('layouts.guest', ['mainClass' => 'guest-main-wide'])

@section('title', 'Términos de uso — ' . config('app.name'))
@section('robots', 'index, follow')
@section('canonical', route('legal.terminos'))
@section('meta_description', 'Términos de uso de PresuFactura, servicio gratuito de presupuestos y facturas para autónomos en España.')

@section('content')
<div class="legal-page">
    <h1>Términos de uso</h1>
    <p><em>Última actualización: julio 2026</em></p>

    <h2>1. Objeto</h2>
    <p>PresuFactura es un servicio online dirigido a autónomos y pequeños negocios en España para crear presupuestos y facturas, enviarlos por email y gestionar recordatorios de cobro.</p>

    <h2>2. Naturaleza de los documentos</h2>
    <p>Con Veri*Factu activado y un certificado válido, PresuFactura puede generar el registro de facturación, incluir código QR y preparar el envío a la AEAT. Sin Veri*Factu, los documentos son <strong>proforma</strong> y no tienen validez fiscal. No afirmamos certificación oficial ni validez automática ante Hacienda. El usuario es responsable de su configuración fiscal y de revisar el cumplimiento con un profesional.</p>

    <h2>3. Registro y cuenta</h2>
    <p>Debes proporcionar datos veraces. Eres responsable de la confidencialidad de tu contraseña y de toda actividad en tu cuenta.</p>

    <h2>4. Gratuidad del servicio</h2>
    <p>PresuFactura es un servicio gratuito. Todas las funciones (documentos ilimitados, recordatorios automáticos, Veri*Factu opcional) están incluidas sin suscripción ni pago. No se requiere tarjeta para registrarse ni para usar el servicio.</p>

    <h2>5. Uso aceptable</h2>
    <p>Queda prohibido usar el servicio para actividades ilegales, suplantación de identidad, envío de spam o cualquier uso que perjudique a terceros o al servicio.</p>

    <h2>6. Limitación de responsabilidad</h2>
    <p>PresuFactura se ofrece «tal cual». No garantizamos disponibilidad ininterrumpida. No somos responsables de pérdidas derivadas del uso de documentos proforma como facturas fiscales.</p>

    <h2>7. Contacto</h2>
    <p>Consultas: <a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a></p>
</div>
@endsection
