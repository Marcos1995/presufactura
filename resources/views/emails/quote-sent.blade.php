@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $document->client->name }},</p>
    <p style="margin:0 0 16px;">Te enviamos el presupuesto proforma <strong>{{ $document->number }}</strong> por un importe de
        <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p style="margin:0 0 8px;">Válido hasta: <strong>{{ $document->valid_until?->format('d/m/Y') }}</strong></p>
    <p style="margin:0 0 16px;">Puedes verlo y aceptarlo online en:<br>
        <a href="{{ $document->publicUrl() }}" style="color:#2563eb;">{{ $document->publicUrl() }}</a></p>
    <p style="margin:0;">El presupuesto va adjunto en PDF.</p>
@endsection
