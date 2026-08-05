@extends('layouts.panel')

@section('title', 'Clientes — ' . config('app.name'))
@section('heading', 'Clientes')

@section('content')
<div class="page-toolbar">
    <a href="{{ route('clients.create') }}" class="btn btn-primary">Nuevo cliente</a>
</div>

@if ($clients->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h2>Sin clientes</h2>
        <p>Añade tus clientes para crear facturas y presupuestos.</p>
        <div class="empty-actions">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">Añadir cliente</a>
        </div>
    </div>
@else
    <div class="card">
        <table class="data-table data-table-list">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>NIF</th>
                    <th>Teléfono</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $client)
                <tr>
                    <td data-label="Nombre">{{ $client->name }}</td>
                    <td data-label="Email">{{ $client->email }}</td>
                    <td data-label="NIF">{{ $client->tax_id ?: '—' }}</td>
                    <td data-label="Teléfono">{{ $client->phone ?: '—' }}</td>
                    <td class="table-actions">
                        <a href="{{ route('clients.edit', $client) }}">Editar</a>
                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline-form" onsubmit="return confirm('¿Eliminar este cliente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-link btn-danger-link">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
