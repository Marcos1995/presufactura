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
<script src="{{ asset('js/invoice-lines.js') }}"></script>
@endpush
