<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $document->user->name }},</p>
    <p>Tu cliente <strong>{{ $document->client->name }}</strong> indica que ha realizado el pago de la factura
        <strong>{{ $document->number }}</strong> ({{ number_format($document->total, 2, ',', '.') }} €).</p>
    <p>Revisa el pago y confirma en tu panel:</p>
    <p><a href="{{ $panelUrl }}">Ver factura en el panel</a></p>
    <p style="font-size: 12px; color: #666;">PresuFactura — notificación automática.</p>
</body>
</html>
