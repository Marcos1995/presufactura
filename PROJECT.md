# PresuFactura — PROJECT

Micro-SaaS para autónomos en España: presupuesto → factura → recordatorios de cobro, con Veri*Factu opcional.

## Stack

- PHP 8.3, Laravel 11, MySQL 8
- Frontend: Blade + HTML/CSS/jQuery (sin SPA)
- PDF: Dompdf
- Email: SMTP
- Veri*Factu: SOAP AEAT, hash encadenado, QR
- Stripe: legado (código residual); la app es 100% gratuita

## Modelo de producto

- Completamente gratis para todos los usuarios
- Sin límites de documentos ni paywall
- Recordatorios, «He pagado» y Veri*Factu incluidos
- UI en es-ES
- Dominio: presufactura.es

## Notas de producción

Ver DEPLOY.md (Hostinger Business, cron, cache, migrate).

```bash
./deploy.sh
php artisan presufactura:smoke-test
```

Cron obligatorio:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

- Nunca commitear `.env`
- Extensiones PHP: openssl, soap, pdo_mysql, mbstring, fileinfo, curl, zip
- `php artisan storage:link` tras deploy
- Veri*Factu: certificado del usuario + `config/verifactu.php` / env AEAT

## Documentación del repo

| Archivo | Uso |
|---------|-----|
| PROJECT.md | Stack, producto, producción (este archivo) |
| AGENTS.md | Cómo deben trabajar agentes de IA |
| FEATURES.md | Catálogo de funcionalidades |
| README.md | Setup local y rutas |
| DEPLOY.md | Deploy Hostinger |
| docs/ | Veri*Factu y prompts históricos |
| .cursor/rules/ | Reglas permanentes del IDE |

## Convenciones

- Minimal diff; sin refactors no pedidos
- Estados factura: draft, sent, expired, paid, payment_pending, cancelled
- Estados presupuesto: draft, sent, accepted, expired
- Factura sin Veri*Factu = proforma + disclaimer
