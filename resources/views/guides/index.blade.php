@extends('layouts.marketing')

@section('title', 'Guías para autónomos — '.config('app.name'))
@section('meta_description', 'Guías prácticas: cómo facturar, presupuestos, Veri*Factu, reclamar cobros y dejar Excel. PresuFactura es gratis para autónomos en España.')
@section('og_title', 'Guías PresuFactura para autónomos')
@section('canonical', route('guides.index'))

@section('content')
<section class="guide-page">
    <h1>Guías para autónomos</h1>
    <p class="guide-lead">Cómo facturar, presupuestar y cobrar sin perderte en Excel ni en Hacienda. Cada guía termina con un paso para probar PresuFactura.</p>

    <ul class="guide-list">
        @foreach ($guides as $slug => $guide)
            <li>
                <a href="{{ route('guides.show', $slug) }}">
                    <strong>{{ $guide['heading'] }}</strong>
                    <span>{{ $guide['lead'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="guide-cta">
        <a href="{{ route('register') }}" class="btn btn-primary" data-analytics="signup_cta_click">Empezar gratis</a>
    </div>
</section>
@endsection
