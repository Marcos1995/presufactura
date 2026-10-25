@extends('layouts.marketing')

@section('title', 'Resultado comprobación — '.config('app.name'))
@section('robots', 'noindex')

@section('content')
<section class="guide-page">
    <h1>Checklist orientativo</h1>
    <p class="alert alert-warning">{{ $result['notes'] }}</p>
    <ul>
        @foreach ($labels as $key => $label)
            <li>{{ ($result['found'][$key] ?? false) ? '✓' : '✗' }} {{ $label }}</li>
        @endforeach
    </ul>
    @if (count($result['missing']))
        <p><strong>Posibles faltas:</strong> {{ implode('; ', $result['missing']) }}</p>
    @endif
    <p>
        <a href="{{ route('register') }}" class="btn btn-primary" data-analytics="signup_cta_click">Pasar a PresuFactura (gratis)</a>
        <a href="{{ route('tools.verifactu.checker') }}" class="btn btn-secondary">Otra factura</a>
    </p>
</section>
@endsection
