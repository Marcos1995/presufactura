<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $document->client->name }},</p>
    <p>Te recordamos que la factura <strong>{{ $document->number }}</strong> venció hace {{ $daysOverdue }} días
        y tiene un importe pendiente de <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p>Fecha de vencimiento: {{ $document->due_date->format('d/m/Y') }}</p>
    @if ($document->user->iban)
        <p>IBAN: <strong>{{ $document->user->iban }}</strong></p>
    @endif
    <p>Gracias.</p>
    <p style="font-size: 12px; color: #666;">{{ $document->user->business_name ?: $document->user->name }}</p>
</body>
</html>
