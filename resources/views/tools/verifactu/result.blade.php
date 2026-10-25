@extends('layouts.marketing')

@section('title', 'Resultado Veri*Factu — '.config('app.name'))
@section('robots', 'noindex')

@section('content')
<section class="guide-page">
    <h1>Tu resultado (orientativo)</h1>
    <p class="alert alert-info">{{ $result['summary'] }}</p>
    <ol>
        @foreach ($result['steps'] as $step)
            <li>{{ $step }}</li>
        @endforeach
    </ol>
    <p>{{ $result['cta'] }}</p>
    <p>
        <a href="{{ route('register') }}" class="btn btn-primary" data-analytics="signup_cta_click">Crear cuenta gratis</a>
        <a href="{{ $aeatUrl }}" class="btn btn-secondary" rel="noopener noreferrer" target="_blank">AEAT</a>
        <a href="{{ route('tools.verifactu') }}" class="btn btn-secondary">Repetir test</a>
    </p>
</section>
@endsection
