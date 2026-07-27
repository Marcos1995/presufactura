<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $document->user->name }},</p>
    <p>La factura <strong>{{ $document->number }}</strong> para <strong>{{ $document->client->name }}</strong>
        venció hace {{ $daysOverdue }} días ({{ number_format($document->total, 2, ',', '.') }} €).</p>
    <p><strong>¿Ya la cobraste?</strong></p>
    <p>
        <a href="{{ $confirmUrl }}" style="display:inline-block;padding:10px 20px;background:#16a34a;color:#fff;text-decoration:none;border-radius:6px;">Sí, marcar como cobrada</a>
    </p>
    <p><a href="{{ $panelUrl }}">Ver factura en PresuFactura</a></p>
</body>
</html>
