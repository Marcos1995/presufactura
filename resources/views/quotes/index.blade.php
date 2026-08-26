@extends('layouts.panel')

@section('title', 'Presupuestos — ' . config('app.name'))
@section('heading', 'Presupuestos')

@section('content')
<div class="page-toolbar">
    <a href="{{ route('quotes.create') }}" class="btn btn-primary">Nuevo presupuesto</a>
</div>

@if ($quotes->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
        </div>
        <h2>Sin presupuestos</h2>
        <p>Crea presupuestos y conviértelos en facturas con un click.</p>
        <div class="empty-actions">
            <form method="POST" action="{{ route('quickstart.quote') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Crear presupuesto de prueba</button>
            </form>
            <a href="{{ route('quotes.create') }}" class="btn btn-secondary">Nuevo presupuesto</a>
        </div>
    </div>
@else
    <div class="card">
        <table class="data-table data-table-list">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Válido hasta</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotes as $quote)
                <tr data-href="{{ route('quotes.show', $quote) }}">
                    <td data-label="Número"><a href="{{ route('quotes.show', $quote) }}">{{ $quote->number }}</a></td>
                    <td data-label="Cliente">{{ $quote->client->name }}</td>
                    <td data-label="Válido hasta">{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</td>
                    <td data-label="Estado"><span class="badge badge-{{ $quote->status }}">{{ $quote->statusLabel() }}</span></td>
                    <td data-label="Total" class="text-right">{{ number_format($quote->total, 2, ',', '.') }} €</td>
                    <td class="table-actions">
                        <a href="{{ route('quotes.show', $quote) }}">Ver</a>
                        <a href="{{ route('quotes.pdf', $quote) }}">PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
