@extends('layouts.marketing')

@section('title', $result['title'].' — '.config('app.name'))
@section('robots', 'noindex')

@section('content')
<section class="guide-page">
    <h1>{{ $result['title'] }}</h1>
    <p class="alert alert-warning">{{ $result['disclaimer'] }}</p>
    <h2>Qué es</h2>
    <p>{{ $result['what'] }}</p>
    <h2>Qué debes hacer</h2>
    <p>{{ $result['actions'] }}</p>
    <h2>Plazo</h2>
    <p>{{ $result['deadline'] ?: 'No se ha podido fijar un plazo concreto en el texto. Léelo en la carta original.' }}</p>
    <p><a class="btn btn-primary" href="{{ $result['official_url'] }}" rel="noopener noreferrer" target="_blank">Trámite / sede oficial</a></p>
    @if ($result['deadline_iso'])
        <p>
            <a class="btn btn-secondary" href="{{ route('tools.cartas.ics', ['title' => $result['title'], 'date' => $result['deadline_iso'], 'url' => $result['official_url']]) }}">Añadir plazo al calendario (.ics)</a>
        </p>
    @endif
    <p><a href="{{ route('tools.cartas') }}">Otra carta</a></p>
</section>
@endsection
