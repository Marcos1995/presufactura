@extends('layouts.guest')

@section('title', 'Política de cookies — ' . config('app.name'))

@section('content')
<div class="legal-page">
    <h1>Política de cookies</h1>
    <p><em>Última actualización: julio 2026</em></p>

    <h2>1. ¿Qué son las cookies?</h2>
    <p>Las cookies son pequeños archivos que se almacenan en tu dispositivo al visitar un sitio web.</p>

    <h2>2. Cookies que utilizamos</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cookie</th>
                <th>Tipo</th>
                <th>Finalidad</th>
                <th>Duración</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>laravel_session</td>
                <td>Técnica (necesaria)</td>
                <td>Mantener la sesión de usuario autenticado</td>
                <td>Sesión</td>
            </tr>
            <tr>
                <td>XSRF-TOKEN</td>
                <td>Técnica (necesaria)</td>
                <td>Protección CSRF</td>
                <td>Sesión</td>
            </tr>
            <tr>
                <td>remember_web_*</td>
                <td>Técnica (opcional)</td>
                <td>Recordar sesión si marcas «Recordarme»</td>
                <td>5 años</td>
            </tr>
        </tbody>
    </table>

    <h2>3. Cookies de terceros</h2>
    <p>Stripe puede establecer cookies durante el proceso de pago. Consulta la <a href="https://stripe.com/es/privacy">política de privacidad de Stripe</a>.</p>

    <h2>4. Gestión</h2>
    <p>Puedes configurar tu navegador para bloquear cookies, aunque el servicio podría dejar de funcionar correctamente (especialmente la sesión).</p>

    <h2>5. Contacto</h2>
    <p><a href="mailto:facturas@presufactura.es">facturas@presufactura.es</a></p>
</div>
@endsection
