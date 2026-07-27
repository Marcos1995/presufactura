@extends('layouts.marketing')

@section('title', 'Centro de ayuda — ' . config('app.name'))
@section('meta_description', 'Preguntas frecuentes sobre PresuFactura: Verifactu, planes Free y Pro, cancelación, privacidad y uso para autónomos.')
@section('og_title', 'Ayuda PresuFactura — FAQ para autónomos')

@section('content')
<section class="help-page">
    <h1>Centro de ayuda</h1>
    <p class="section-sub">Respuestas claras sobre PresuFactura, planes, Verifactu y tus datos.</p>

    @include('partials.faq-list', ['faqs' => $faqs])

    <div class="help-contact">
        <h2>¿No encuentras lo que buscas?</h2>
        <p>Escríbenos a <a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a></p>
        <p><a href="{{ route('register') }}" class="btn btn-primary">Probar PresuFactura gratis</a></p>
    </div>
</section>
@endsection
