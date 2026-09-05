@php
    $colspan = $colspan ?? 4;
@endphp
<tr>
    <td colspan="{{ $colspan }}" class="text-right"><strong>Subtotal</strong></td>
    <td class="text-right">{{ number_format($document->subtotal, 2, ',', '.') }} €</td>
</tr>
<tr>
    <td colspan="{{ $colspan }}" class="text-right"><strong>IVA</strong></td>
    <td class="text-right">{{ number_format($document->vat_amount, 2, ',', '.') }} €</td>
</tr>
@if ((float) $document->recargo_amount > 0)
<tr>
    <td colspan="{{ $colspan }}" class="text-right"><strong>Recargo</strong></td>
    <td class="text-right">{{ number_format($document->recargo_amount, 2, ',', '.') }} €</td>
</tr>
@endif
@if ((float) $document->irpf_amount > 0)
<tr>
    <td colspan="{{ $colspan }}" class="text-right"><strong>IRPF</strong></td>
    <td class="text-right">− {{ number_format($document->irpf_amount, 2, ',', '.') }} €</td>
</tr>
@endif
<tr>
    <td colspan="{{ $colspan }}" class="text-right"><strong>Total</strong></td>
    <td class="text-right"><strong>{{ number_format($document->total, 2, ',', '.') }} €</strong></td>
</tr>
