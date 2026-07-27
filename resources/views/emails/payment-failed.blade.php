@extends('emails.layout')

@section('content')
    <p style="margin:0 0 16px;">Hola {{ $user->name }},</p>
    <p style="margin:0 0 16px;">No hemos podido cobrar tu suscripción <strong>Pro</strong> de PresuFactura (12 €/mes).</p>
    <p style="margin:0 0 16px;">Stripe reintentará el cargo automáticamente. Si el problema persiste, tu plan Pro podría cancelarse.</p>
    <p style="margin:0;">
        <a href="{{ $subscriptionUrl }}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">Actualizar método de pago</a>
    </p>
@endsection
