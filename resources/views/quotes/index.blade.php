@extends('layouts.panel')

@section('title', 'Presupuestos — ' . config('app.name'))
@section('heading', 'Presupuestos')

@section('content')
<div class="page-toolbar">
    @if (auth()->user()->canCreateDocument())
        <a href="{{ route('quotes.create') }}" class="btn btn-primary">Nuevo presupuesto</a>
    @else
        <span class="text-muted">Límite Free alcanzado (3 docs/mes)</span>
    @endif
</div>

@if ($quotes->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
        </div>
        <h2>Sin presupuestos</h2>
        <p>Crea presupuestos y conviértelos en facturas con un click.</p>
        @if (auth()->user()->canCreateDocument())
        <div class="empty-actions">
            <a href="{{ route('quotes.create') }}" class="btn btn-primary">Nuevo presupuesto</a>
        </div>
        @endif
    </div>
@else
    <div class="card">
        <table class="data-table">
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
                    <td><a href="{{ route('quotes.show', $quote) }}">{{ $quote->number }}</a></td>
                    <td>{{ $quote->client->name }}</td>
                    <td>{{ $quote->valid_until?->format('d/m/Y') ?? '—' }}</td>
                    <td><span class="badge badge-{{ $quote->status }}">{{ $quote->statusLabel() }}</span></td>
                    <td class="text-right">{{ number_format($quote->total, 2, ',', '.') }} €</td>
                    <td class="table-actions"><a href="{{ route('quotes.show', $quote) }}">Ver</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
