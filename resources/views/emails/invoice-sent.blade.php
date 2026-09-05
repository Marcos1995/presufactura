@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $document->client->name }},</p>
    <p style="margin:0 0 16px;">Te enviamos {{ $document->isFiscal() ? 'la factura' : 'la factura proforma' }} <strong>{{ $document->number }}</strong> por un importe de
        <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p style="margin:0 0 8px;">Fecha de vencimiento: <strong>{{ $document->due_date->format('d/m/Y') }}</strong></p>
    <p style="margin:0 0 16px;">Puedes ver la factura y confirmar el pago en:<br>
        <a href="{{ $document->publicUrl() }}" style="color:#2563eb;">{{ $document->publicUrl() }}</a></p>
    @if ($document->issuerIban())
        <p style="margin:0 0 16px;">IBAN para transferencia: <strong>{{ $document->issuerIban() }}</strong></p>
    @endif
    <p style="margin:0;">La factura va adjunta en PDF.</p>
@endsection
