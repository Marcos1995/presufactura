@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $document->user->name }},</p>
    <p style="margin:0 0 16px;">La factura <strong>{{ $document->number }}</strong> para <strong>{{ $document->client->name }}</strong>
        venció hace {{ $daysOverdue }} días ({{ number_format($document->total, 2, ',', '.') }} €).</p>
    <p style="margin:0 0 16px;"><strong>¿Ya la cobraste?</strong></p>
    <p style="margin:0 0 16px;">
        <a href="{{ $confirmUrl }}" style="display:inline-block;padding:12px 24px;background:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Sí, marcar como cobrada</a>
    </p>
    <p style="margin:0;"><a href="{{ $panelUrl }}" style="color:#2563eb;">Ver factura en PresuFactura</a></p>
@endsection
