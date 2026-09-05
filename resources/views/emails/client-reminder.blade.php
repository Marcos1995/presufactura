@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $document->client->name }},</p>
    <p style="margin:0 0 16px;">Te recordamos que la factura <strong>{{ $document->number }}</strong> venció hace {{ $daysOverdue }} días
        y tiene un importe pendiente de <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p style="margin:0 0 8px;">Fecha de vencimiento: {{ $document->due_date->format('d/m/Y') }}</p>
    @if ($document->issuerIban())
        <p style="margin:0 0 16px;">IBAN: <strong>{{ $document->issuerIban() }}</strong></p>
    @endif
    <p style="margin:0;">Gracias.<br><span style="color:#6b7280;">{{ $document->issuerName() }}</span></p>
@endsection
