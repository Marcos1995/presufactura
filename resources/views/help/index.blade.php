@extends('layouts.marketing')

@section('title', 'Centro de ayuda — ' . config('app.name'))
@section('meta_description', 'Preguntas frecuentes sobre PresuFactura: Verifactu, gratuidad, privacidad y uso para autónomos.')
@section('og_title', 'Ayuda PresuFactura — FAQ para autónomos')

@section('content')
<section class="help-page">
    <div class="help-intro">
        <h1>Centro de ayuda</h1>
        <p class="section-sub">Respuestas claras sobre PresuFactura, Veri*Factu y tus datos.</p>
    </div>

    <div class="help-verifactu card card-narrow">
        <h2>Veri*Factu</h2>
        <p>Activa Veri*Factu en <strong>Configuración</strong>, sube tu certificado electrónico .p12 y elige la modalidad de envío a AEAT. Cada factura enviada genera un registro SIF con hash encadenado, código QR en el PDF y remisión automática a Hacienda. Si AEAT acepta el registro, puedes anular la factura desde su detalle; se creará un registro de anulación encadenado.</p>
    </div>

    @include('partials.faq-list', ['faqs' => $faqs])

    <div class="help-contact">
        <h2>¿No encuentras lo que buscas?</h2>
        <p>Escríbenos a <a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a></p>
        <p><a href="{{ route('register') }}" class="btn btn-primary">Probar PresuFactura gratis</a></p>
    </div>
</section>
@endsection
