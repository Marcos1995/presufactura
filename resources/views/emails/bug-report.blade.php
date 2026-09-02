@extends('emails.layout')

@section('email_title', 'Aviso de fallo — PresuFactura')

@section('content')
    <p style="margin:0 0 16px;">Nuevo aviso de fallo en PresuFactura.</p>
    <p style="margin:0 0 8px;"><strong>Usuario:</strong> {{ $report->user->name }} ({{ $report->user->email }})</p>
    <p style="margin:0 0 8px;"><strong>Página:</strong> {{ $report->page_url }}</p>
    <p style="margin:0 0 16px;"><strong>Navegador:</strong> {{ $report->user_agent ?: '—' }}</p>
    <p style="margin:0 0 8px;"><strong>Descripción:</strong></p>
    <p style="margin:0;white-space:pre-wrap;">{{ $report->description }}</p>
@endsection
