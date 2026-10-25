<p>Hola {{ $user->name }},</p>
<p>En unos 10 días vence (orientativo): <strong>{{ $obligation['name'] }}</strong> — {{ $obligation['period'] }} ({{ $obligation['due'] }}).</p>
@if ($estimate)
<p>Estimación solo desde tus facturas en PresuFactura: base {{ number_format($estimate['subtotal'], 2, ',', '.') }} €, IVA {{ number_format($estimate['vat'], 2, ',', '.') }} €, IRPF {{ number_format($estimate['irpf'], 2, ',', '.') }} € ({{ $estimate['invoices'] }} facturas). {{ $estimate['note'] }}</p>
@endif
<p><a href="{{ $obligation['url'] }}">Sede AEAT</a> · <a href="{{ route('fiscal.calendar') }}">Tu calendario</a></p>
<p>Esto no es un borrador oficial ni un aviso de la Administración.</p>
