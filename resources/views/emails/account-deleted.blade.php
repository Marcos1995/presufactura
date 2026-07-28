@extends('emails.layout')

@section('email_title', 'Cuenta eliminada — PresuFactura')

@section('content')
    <p style="margin:0 0 16px;">Hola <strong>{{ $userName }}</strong>,</p>
    <p style="margin:0 0 16px;">Confirmamos que tu cuenta en PresuFactura y todos los datos asociados (clientes, documentos, configuración fiscal) han sido eliminados de forma inmediata.</p>
    <p style="margin:0;font-size:13px;color:#6b7280;">Si no solicitaste esta baja, contacta con nosotros en <a href="mailto:facturas@presufactura.es" style="color:#2563eb;">facturas@presufactura.es</a>.</p>
@endsection
