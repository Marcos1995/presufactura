@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $document->user->name }},</p>
    <p style="margin:0 0 16px;">Tu cliente <strong>{{ $document->client->name }}</strong> indica que ha realizado el pago de la factura
        <strong>{{ $document->number }}</strong> ({{ number_format($document->total, 2, ',', '.') }} €).</p>
    <p style="margin:0 0 16px;">Revisa el pago y confirma en tu panel:</p>
    <p style="margin:0;">
        <a href="{{ $panelUrl }}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Ver factura</a>
    </p>
@endsection
