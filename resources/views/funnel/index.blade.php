@extends('layouts.panel')

@section('title', 'Embudo — '.config('app.name'))
@section('heading', 'Embudo (30 días)')

@section('content')
<p class="text-muted">Solo eventos sin datos personales. Bots y tráfico interno van aparte.</p>

<div class="dashboard-grid">
    @foreach ($funnel as $name => $count)
        <div class="stat-card">
            <span class="stat-label">{{ $name }}</span>
            <span class="stat-value">{{ $count }}</span>
        </div>
    @endforeach
</div>

<div class="card">
    <h2 class="section-title">Tráfico</h2>
    <table class="data-table">
        <tbody>
            <tr><td>Portada / público</td><td class="text-right">{{ $traffic['landing'] }}</td></tr>
            <tr><td>Zona autenticada</td><td class="text-right">{{ $traffic['authenticated'] }}</td></tr>
            <tr><td>Tráfico propio</td><td class="text-right">{{ $traffic['internal'] }}</td></tr>
            <tr><td>Bots y rastreadores</td><td class="text-right">{{ $traffic['bot'] }}</td></tr>
            <tr><td>Errores 4xx</td><td class="text-right">{{ $traffic['http_4xx'] }}</td></tr>
            <tr><td>Errores 5xx</td><td class="text-right">{{ $traffic['http_5xx'] }}</td></tr>
        </tbody>
    </table>
</div>
@endsection
