@extends('layouts.guest', ['mainClass' => 'guest-main-wide'])

@section('title', 'Política de privacidad — ' . config('app.name'))

@section('content')
<div class="legal-page">
    <h1>Política de privacidad</h1>
    <p><em>Última actualización: julio 2026</em></p>

    <h2>1. Responsable del tratamiento</h2>
    <p>PresuFactura (presufactura.es)<br>
    Email: <a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a></p>

    <h2>2. Datos que recogemos</h2>
    <ul>
        <li><strong>Datos de cuenta:</strong> nombre, email, contraseña (cifrada).</li>
        <li><strong>Datos fiscales:</strong> nombre comercial, NIF/CIF, dirección, IBAN, logo.</li>
        <li><strong>Datos de clientes:</strong> los que introduces para emitir documentos.</li>
        <li><strong>Datos de facturación:</strong> gestionados por Stripe (no almacenamos datos de tarjeta).</li>
    </ul>

    <h2>3. Finalidad y base legal</h2>
    <p>Tratamos tus datos para prestarte el servicio (ejecución del contrato, art. 6.1.b RGPD) y cumplir obligaciones legales. Los emails transaccionales (facturas, recordatorios) son necesarios para el servicio.</p>

    <h2>4. Conservación</h2>
    <p>Conservamos los datos mientras mantengas tu cuenta activa. Tras la baja, se eliminarán en un plazo máximo de 30 días, salvo obligación legal de conservación.</p>

    <h2>5. Destinatarios</h2>
    <ul>
        <li>Proveedor de hosting (servidores en UE).</li>
        <li>Stripe (pagos).</li>
        <li>Proveedor SMTP (envío de emails).</li>
    </ul>

    <h2>6. Tus derechos</h2>
    <p>Puedes ejercer los derechos de acceso, rectificación, supresión, limitación, portabilidad y oposición escribiendo a <a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a>. También puedes reclamar ante la AEPD (<a href="https://www.aepd.es">www.aepd.es</a>).</p>

    <h2>7. Seguridad</h2>
    <p>Aplicamos medidas técnicas y organizativas para proteger tus datos (HTTPS, contraseñas hasheadas, acceso restringido).</p>
</div>
@endsection
