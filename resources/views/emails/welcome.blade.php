@extends('emails.layout')

@section('email_title', 'Bienvenido — PresuFactura')

@section('content')
    <p style="margin:0 0 16px;">Hola <strong>{{ $user->name }}</strong>,</p>
    <p style="margin:0 0 16px;">Gracias por registrarte en <strong>PresuFactura</strong>. Ya puedes crear presupuestos y facturas proforma, enviarlos por email y hacer seguimiento de cobros.</p>
    <p style="margin:0 0 20px;">Empieza configurando tu perfil fiscal (NIF, IBAN, logo) — te lleva menos de 3 minutos.</p>
    <p style="margin:0 0 24px;">
        <a href="{{ route('onboarding.step', ['step' => 1]) }}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Configurar mi perfil</a>
    </p>
    <p style="margin:0;font-size:13px;color:#6b7280;">Plan Free: 3 documentos al mes sin tarjeta. Actualiza a Pro cuando necesites más.</p>
@endsection
