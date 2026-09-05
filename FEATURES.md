# FEATURES.md — PresuFactura

Catálogo de funcionalidades. Todo incluido, sin planes de pago.

## Acceso y cuenta

- Registro / login (email o Google) / verificación email / reset password
- Onboarding 3 pasos (datos fiscales, IBAN, preferencias + recordatorios)
- Una o varias empresas emisoras por usuario, con selector en el panel
- Configuración de empresa: logo, NIF, dirección, IVA, IRPF, recargo, prefijos, vencimiento
- Baja de cuenta (RGPD)

## Clientes

- CRUD clientes
- Datos fiscales y de contacto para documentos

## Presupuestos

- CRUD, estados: draft, sent, accepted, expired, rejected
- PDF + email al publicar
- Enlace público `/p/{token}` con aceptación o rechazo online
- Conversión a factura borrador (desde borrador, enviado o aceptado)
- Presupuesto de prueba desde el dashboard (primer valor sin Veri*Factu)

## Facturas

- CRUD, estados: draft, sent, expired, paid, payment_pending, cancelled
- Líneas con IVA, IRPF y recargo de equivalencia
- PDF + email
- Enlace público con IBAN y botón «He pagado»
- Rectificativas e inmutabilidad cuando aplica Veri*Factu
- Anulación con registro SIF

## Cobros y recordatorios

- Cron `presufactura:process-reminders`
- Recordatorios al cliente (días configurables, p. ej. +3/+7/+14)
- Aviso al autónomo «¿cobraste?» (día configurable, p. ej. +10)
- Dashboard: por cobrar, vencido, cobrado mes, docs del mes
- Feedback opcional tras el primer documento

## Analítica (sin datos personales)

- Eventos de embudo: visita, clic, registro, primer presupuesto, primera factura, PDF, email, Veri*Factu
- No se registran nombres, NIF, importes, emails ni contenido de facturas
- Separación de portada, zona autenticada, tráfico propio, bots y errores 4xx/5xx
- Panel `/embudo` (cuenta demo) y `php artisan presufactura:funnel`

## Veri*Factu (opcional por empresa)

- Activación por defecto al registrarse; certificado .p12 para emitir fiscales y enviar a AEAT
- Comando `php artisan presufactura:verifactu-check` (producción: schema + sandbox demo, sin AEAT)
- Comando `php artisan presufactura:verifactu-prove` (demostración con rollback, sin AEAT real)
- Catálogo de ejemplo (`DEMO_ADMIN_EMAIL=marcospc1995@gmail.com` + `php artisan presufactura:prepare-test-user`)
- Certificado de desarrollo autofirmado (`php artisan presufactura:verifactu-dev-cert`): flujo interno, no AEAT
- Hash encadenado, XML SIF, QR en PDF
- Envío AEAT (SOAP), reintentos, badges de estado
- Export SIF, entorno de pruebas documentado en `docs/`

## Marketing y legal

- Landing, /precios, /ayuda, /guias (contenido público)
- Términos, privacidad, cookies
- Emails branded (welcome, documentos, recordatorios)
- Login, registro, dashboard y documentos privados: noindex

## Legado (no expuesto como producto de pago)

- Rutas/servicios Stripe y columna `users.plan` conservados por compatibilidad
- UI no ofrece upgrade ni suscripción de pago
- `isPro()` / `canCreateDocument()` = acceso completo para todos
