@extends('layouts.panel')

@section('title', ($client->exists ? 'Editar' : 'Nuevo') . ' cliente — ' . config('app.name'))
@section('heading', $client->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
<div class="card card-narrow">
    <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}" class="form">
        @csrf
        @if ($client->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="name">Nombre *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $client->name) }}" required>
            @error('name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $client->email) }}" required>
            @error('email')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="tax_id">NIF/CIF</label>
            <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id', $client->tax_id) }}">
            @error('tax_id')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea id="address" name="address" rows="2">{{ old('address', $client->address) }}</textarea>
            @error('address')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
            @error('phone')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $client->exists ? 'Guardar' : 'Crear cliente' }}</button>
        </div>
    </form>
</div>
@endsection
