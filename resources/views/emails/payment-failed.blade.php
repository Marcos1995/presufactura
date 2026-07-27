<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $user->name }},</p>
    <p>No hemos podido cobrar tu suscripción <strong>Pro</strong> de PresuFactura (12 €/mes).</p>
    <p>Stripe reintentará el cargo automáticamente. Si el problema persiste, tu plan Pro podría cancelarse.</p>
    <p>Actualiza tu método de pago aquí:</p>
    <p><a href="{{ $subscriptionUrl }}">{{ $subscriptionUrl }}</a></p>
    <p style="font-size: 12px; color: #666;">PresuFactura — facturas@presufactura.es</p>
</body>
</html>
