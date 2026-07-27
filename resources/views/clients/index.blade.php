@extends('layouts.panel')

@section('title', 'Clientes — ' . config('app.name'))
@section('heading', 'Clientes')

@section('content')
<div class="page-toolbar">
    <a href="{{ route('clients.create') }}" class="btn btn-primary">Nuevo cliente</a>
</div>

@if ($clients->isEmpty())
    <div class="empty-state">
        <h2>Sin clientes</h2>
        <p>Añade tus clientes para crear facturas y presupuestos.</p>
        <div class="empty-actions">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">Añadir cliente</a>
        </div>
    </div>
@else
    <div class="card">
        <table class="data-table">
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
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->tax_id ?: '—' }}</td>
                    <td>{{ $client->phone ?: '—' }}</td>
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
