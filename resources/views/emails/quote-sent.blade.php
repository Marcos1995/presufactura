<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #111; line-height: 1.5;">
    <p>Hola {{ $document->client->name }},</p>
    <p>Te enviamos el presupuesto proforma <strong>{{ $document->number }}</strong> por un importe de
        <strong>{{ number_format($document->total, 2, ',', '.') }} €</strong>.</p>
    <p>Válido hasta: <strong>{{ $document->valid_until?->format('d/m/Y') }}</strong></p>
    <p>Puedes verlo y aceptarlo online en: <a href="{{ $document->publicUrl() }}">{{ $document->publicUrl() }}</a></p>
    <p>El presupuesto va adjunto en PDF.</p>
    <p style="font-size: 12px; color: #666;">Documento proforma. Sin validez fiscal. Generado con PresuFactura.</p>
</body>
</html>
