@extends('layouts.panel')

@section('title', 'Nueva factura — ' . config('app.name'))
@section('heading', 'Nueva factura')

@section('content')
@include('invoices._form', [
    'action' => route('invoices.store'),
    'method' => 'POST',
    'invoice' => null,
    'clients' => $clients,
    'defaultVatRate' => $defaultVatRate,
    'lineItems' => $lineItems,
])
@endsection

@push('scripts')
@include('layouts.partials.invoice-lines-js')
@endpush
