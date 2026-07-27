<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $document->client->name }},</p>
    <p>Te enviamos la factura proforma <strong>{{ $document->number }}</strong> por un importe de
        <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p>Fecha de vencimiento: <strong>{{ $document->due_date->format('d/m/Y') }}</strong></p>
    <p>Puedes ver la factura y confirmar el pago en: <a href="{{ $document->publicUrl() }}">{{ $document->publicUrl() }}</a></p>
    @if ($document->user->iban)
        <p>IBAN para transferencia: <strong>{{ $document->user->iban }}</strong></p>
    @endif
    <p>La factura va adjunta en PDF.</p>
    <p style="font-size: 12px; color: #666;">Documento proforma. Sin validez fiscal. Generado con PresuFactura.</p>
</body>
</html>
