# FEATURES.md — PresuFactura

Catálogo de funcionalidades. Todo incluido, sin planes de pago.

## Acceso y cuenta

- Registro / login / verificación email / reset password
- Onboarding 3 pasos (datos fiscales, IBAN, preferencias + recordatorios)
- Configuración de perfil: logo, NIF, dirección, IVA, prefijos, vencimiento
- Baja de cuenta (RGPD)

## Clientes

- CRUD clientes
- Datos fiscales y de contacto para documentos

## Presupuestos

- CRUD, estados: draft, sent, accepted, expired
- PDF + email al publicar
- Enlace público `/p/{token}` con aceptación online
- Conversión a factura borrador

## Facturas

- CRUD, estados: draft, sent, expired, paid, payment_pending, cancelled
- PDF + email
- Enlace público con IBAN y botón «He pagado»
- Rectificativas e inmutabilidad cuando aplica Veri*Factu
- Anulación con registro SIF

## Cobros y recordatorios

- Cron `presufactura:process-reminders`
- Recordatorios al cliente (días configurables, p. ej. +3/+7/+14)
- Aviso al autónomo «¿cobraste?» (día configurable, p. ej. +10)
- Dashboard: por cobrar, vencido, cobrado mes, docs del mes

## Veri*Factu (opcional por usuario)

- Activación por defecto al registrarse; certificado .p12 para emitir fiscales y enviar a AEAT
- Comando `php artisan presufactura:verifactu-prove` (demostración con rollback, sin AEAT real)
- Catálogo de ejemplo (`DEMO_ADMIN_EMAIL=marcospc1995@gmail.com` + `php artisan presufactura:prepare-test-user`)
- Certificado de desarrollo autofirmado (`php artisan presufactura:verifactu-dev-cert`): flujo interno, no AEAT
- Hash encadenado, XML SIF, QR en PDF
- Envío AEAT (SOAP), reintentos, badges de estado
- Export SIF, entorno de pruebas documentado en `docs/`

## Marketing y legal

- Landing, /precios (plan único gratis), /ayuda (FAQ)
- Términos, privacidad, cookies
- Emails branded (welcome, documentos, recordatorios)

## Legado (no expuesto como producto de pago)

- Rutas/servicios Stripe y columna `users.plan` conservados por compatibilidad
- UI no ofrece upgrade ni suscripción de pago
- `isPro()` / `canCreateDocument()` = acceso completo para todos
