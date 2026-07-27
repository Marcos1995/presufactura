# Deploy — Hostinger Business (presufactura.es)

## Estructura en servidor

```
~/domains/presufactura.es/
├── laravel/                 # Repo Git (composer, artisan, app…)
│   ├── app/
│   ├── public/
│   │   ├── css/
│   │   ├── js/
│   │   └── storage/       # symlink → storage/app/public
│   └── .env                 # NUNCA en Git
└── public_html/             # Document root (dominio apunta aquí)
    ├── index.php            # Copia/symlink desde repo index.php
    ├── .htaccess            # Copia/symlink desde repo .htaccess
    ├── css → ../laravel/public/css
    ├── js  → ../laravel/public/js
    └── storage → ../laravel/public/storage
```

## Requisitos Hostinger

- PHP **8.3** (Selector PHP en hPanel)
- Extensiones: `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `zip`, `gd`
- MySQL 8.x
- SSH activo (Business)
- Cron jobs (hPanel → Avanzado → Cron)

## 1. Clonar / actualizar código

```bash
cd ~/domains/presufactura.es
git clone <repo-url> laravel
# o actualizar:
cd laravel && ./deploy.sh
```

## 2. Enlaces public_html

Desde `~/domains/presufactura.es`:

```bash
cp laravel/index.php public_html/index.php
cp laravel/.htaccess public_html/.htaccess
ln -sfn ../laravel/public/css public_html/css
ln -sfn ../laravel/public/js public_html/js
```

## 3. Composer y Laravel

```bash
cd laravel
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edita `.env` (ver sección variables). **No commitear `.env`.**

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan presufactura:smoke-test
```

## 4. Variables `.env` producción

```env
APP_NAME=PresuFactura
APP_ENV=production
APP_DEBUG=false
APP_URL=https://presufactura.es

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_base
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=facturas@presufactura.es
MAIL_PASSWORD="tu_password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=facturas@presufactura.es
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_ID=price_...
```

## 5. Cron (recordatorios)

hPanel → Cron → cada minuto:

```bash
* * * * * cd /home/USUARIO/domains/presufactura.es/laravel && php artisan schedule:run >> /dev/null 2>&1
```

Ajusta la ruta `USUARIO` a tu cuenta Hostinger.

Manual: `php artisan presufactura:process-reminders`

## 6. Stripe

1. **Checkout** — producto Pro 12 €/mes **recurrente**, copia `price_…` **live** → `STRIPE_PRICE_ID`
2. Claves **live** en `.env`: `sk_live_…`, `pk_live_…` (no test en producción)
3. Tras editar `.env`: `php artisan config:cache` (si no, Stripe devuelve error / 500)
4. **Webhook** — URL: `https://presufactura.es/stripe/webhook`  
   Eventos: `checkout.session.completed`, `customer.subscription.deleted`, `invoice.payment_failed`
3. **Customer Portal** — Dashboard → Settings → Billing → Customer portal → Activar  
   Los usuarios Pro gestionan suscripción en `/suscripcion`

## 7. Deploy rutinario

```bash
cd ~/domains/presufactura.es/laravel
./deploy.sh
```

Tras cambiar `.env`:

```bash
php artisan config:cache
php artisan presufactura:smoke-test
```

## 8. Permisos

```bash
chmod -R ug+rwx storage bootstrap/cache
```

## 9. Verificación post-deploy

```bash
php artisan presufactura:smoke-test
```

Checklist manual: registro → onboarding → crear factura → PDF → enviar email.

## Troubleshooting

| Problema | Solución |
|----------|----------|
| 500 en todas las rutas | Revisar `storage/logs/laravel.log`, permisos storage |
| CSS/JS 404 | Symlinks `public_html/css` y `js` |
| Logos 404 | `php artisan storage:link` + symlink `public_html/storage` |
| Emails no llegan | Verificar SMTP en `.env`, smoke-test mail |
| Stripe webhook falla | URL HTTPS, secret correcto, CSRF except en `stripe/webhook` |
