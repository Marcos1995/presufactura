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
                <tr>
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
