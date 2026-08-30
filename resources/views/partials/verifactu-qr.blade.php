@if (! empty($qrDataUri))
<div class="verifactu-qr">
    <img src="{{ $qrDataUri }}" alt="QR Veri*Factu" width="128" height="128">
    <div>
        <strong>VERI*FACTU</strong>
        <p>Factura verificable en la sede electrónica de la AEAT</p>
        @if (! empty($qrUrl))
            <p><a href="{{ $qrUrl }}" target="_blank" rel="noopener">Comprobar en la AEAT</a></p>
        @endif
    </div>
</div>
@endif
