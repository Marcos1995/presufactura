@extends('emails.layout')

@section('email_title', 'Restablecer contraseña — PresuFactura')

@section('content')
    <p style="margin:0 0 16px;">Hola <strong>{{ $user->name }}</strong>,</p>
    <p style="margin:0 0 16px;">Recibimos una solicitud para restablecer la contraseña de tu cuenta en PresuFactura.</p>
    <p style="margin:0 0 24px;">
        <a href="{{ $url }}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Restablecer contraseña</a>
    </p>
    <p style="margin:0;font-size:13px;color:#6b7280;">Este enlace expira en {{ $count }} minutos. Si no solicitaste el cambio, ignora este email.</p>
@endsection
