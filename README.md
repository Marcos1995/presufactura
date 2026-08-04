# PresuFactura

Micro-SaaS para autónomos: presupuesto → factura → recordatorios de cobro.

**Stack:** PHP 8.3, Laravel 11, MySQL, HTML/CSS/jQuery, Dompdf, SMTP, Stripe.

## Requisitos

- PHP 8.3+ (extensiones: `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `zip`)
- Composer 2.x
- MySQL 8.0+

## Setup local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Abre http://localhost:8000 — registro en `/registro`, login en `/login`.

## Fases completadas

| Fase | Contenido |
|------|-----------|
| F1 | Laravel, migraciones, auth, layout panel |
| F2 | CRUD clientes + facturas, PDF, límite Free |
| F3 | Emails, estados, cron recordatorios, dashboard |
| F4 | Presupuestos, link público, Stripe Pro, landing |
| F6 | Perfil editable, onboarding, legal, suscripción, producto usable |
| F7 | DEPLOY.md, deploy.sh, smoke-test, Stripe payment_failed |
| F8 | Landing comercial, /ayuda, WelcomeMail, emails branded, SEO |
| F9 | Veri*Factu: migraciones SIF, modelos, config/verifactu.php |
| F10 | Veri*Factu: HashChainService, XmlBuilderService, BillingRecordService, hook envío factura |
| F11 | Veri*Factu: QrService, QR en PDF, badge fiscal vs proforma |
| F12 | Veri*Factu: AeatSoapClient, SubmitBillingRecordJob, comando retry AEAT |
| F13 | Veri*Factu: UI configuración certificado, badges AEAT, anulación facturas |

## Producción

Ver **[DEPLOY.md](DEPLOY.md)** — Hostinger Business, cron, `.env`, migrate, cache.

```bash
./deploy.sh
php artisan presufactura:smoke-test
```

## Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/` | Landing |
| `/precios` | Página de precios |
| `/registro`, `/login` | Auth |
| `/onboarding/{1-3}` | Wizard configuración inicial |
| `/dashboard` | Panel KPIs |
| `/configuracion` | Editar perfil fiscal y preferencias |
| `/suscripcion` | Plan actual + Stripe Customer Portal |
| `/clientes` | CRUD clientes |
| `/facturas`, `/presupuestos` | CRUD documentos |
| `/facturas/{id}/pdf`, `/presupuestos/{id}/pdf` | Descargar PDF |
| `/p/{token}` | Vista pública presupuesto o factura |
| `/p/{token}/he-pagado` | Cliente indica pago |
| `/terminos`, `/privacidad`, `/cookies` | Páginas legales RGPD |

## Stripe

`.env`: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_ID`

Webhook: `https://presufactura.es/stripe/webhook`

Eventos: `checkout.session.completed`, `customer.subscription.deleted`, `invoice.payment_failed`

Activa Customer Portal en Stripe Dashboard → Settings → Billing → Customer portal.
Usuarios Pro acceden desde `/suscripcion`.

## Cron producción

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Manual: `php artisan presufactura:process-reminders`

## Veri*Factu (F9–F14)

Módulo SIF implementado: registros encadenados, QR en PDF, envío AEAT, anulación e inmutabilidad.

- Plan: **[docs/PLAN-VERIFACTU-IMPLEMENTACION.md](docs/PLAN-VERIFACTU-IMPLEMENTACION.md)**
- Análisis: **[docs/VERIFACTU.md](docs/VERIFACTU.md)**
- Comandos: `presufactura:verifactu-retry-failed`, `presufactura:verifactu-export {user}`

## Notas
- Plan Free: 3 docs/mes. Pro: 12 €/mes, documentos ilimitados.
- **Nunca** commitear `.env`.

## Checklist tests manuales (Fase 6)

<!-- Ejecutar tras deploy o en local con mail/log driver -->

- [ ] Registro nuevo → redirige a onboarding paso 1 → completar 3 pasos → dashboard
- [ ] `/configuracion` PUT: guardar business_name, NIF, IBAN, logo, prefijos, recordatorios
- [ ] Crear presupuesto → Publicar → email cliente con PDF + enlace `/p/{token}`
- [ ] `/presupuestos/{id}/pdf` descarga PDF presupuesto
- [ ] Cliente abre `/p/{token}` presupuesto → Aceptar → estado accepted
- [ ] Enviar factura → cliente abre mismo `/p/{token}` → ve IBAN → «He pagado»
- [ ] «He pagado» → estado payment_pending + email al autónomo
- [ ] Free: crear 4º doc/mes → modal upgrade (no redirect error)
- [ ] `/precios`, `/suscripcion`, Stripe Portal (usuario Pro)
- [ ] Footer links legales en landing, panel y guest
- [ ] `/terminos`, `/privacidad`, `/cookies` cargan contenido español
