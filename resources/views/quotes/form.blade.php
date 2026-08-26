@extends('layouts.panel')

@section('title', 'Nuevo presupuesto — ' . config('app.name'))
@section('heading', 'Nuevo presupuesto')

@section('content')
@include('quotes._form', [
    'action' => route('quotes.store'),
    'method' => 'POST',
    'quote' => null,
    'clients' => $clients,
    'defaultVatRate' => $defaultVatRate,
    'lineItems' => $lineItems,
])
@endsection

@push('scripts')
@include('layouts.partials.invoice-lines-js')
@endpush
