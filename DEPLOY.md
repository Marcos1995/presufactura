# Deploy — Hostinger Business (presufactura.es)

## Estructura en servidor

El repo Git va en **`public_html/laravel/`**. `index.php` y `.htaccess` están en **`public_html/`** (fuera del repo, créalos a mano una vez).

```
public_html/                 # Document root
├── index.php                # NO en Git — ver plantilla abajo
├── .htaccess                # NO en Git — sirve css/js desde laravel/public/
└── laravel/                 # git clone aquí
    ├── app/
    ├── public/css/app.css   # servido en /css/app.css vía .htaccess
    └── .env
```

## Plantillas public_html (fuera del repo)

Crea **`public_html/index.php`**:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravel = __DIR__.'/laravel';

if (file_exists($maintenance = $laravel.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravel.'/vendor/autoload.php';

(require_once $laravel.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

Crea **`public_html/.htaccess`**:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Assets desde laravel/public/ (no copiar css/js a public_html/)
    RewriteRule ^css/(.*)$ laravel/public/css/$1 [L]
    RewriteRule ^js/(.*)$ laravel/public/js/$1 [L]
    RewriteRule ^images/(.*)$ laravel/public/images/$1 [L]
    RewriteRule ^storage/(.*)$ laravel/public/storage/$1 [L]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

Options -Indexes
```

## Requisitos Hostinger

- PHP **8.3** (Selector PHP en hPanel)
- Extensiones: `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `zip`, `gd`
- MySQL 8.x
- SSH activo (Business)
- Cron jobs (hPanel → Avanzado → Cron)

## 1. Clonar / actualizar código

```bash
cd ~/domains/presufactura.es/public_html
git clone <repo-url> laravel
# o actualizar:
cd laravel && ./deploy.sh
```

## 2. Assets (CSS/JS)

El `.htaccess` de arriba sirve `/css/`, `/js/`, `/images/` y `/storage/` desde `laravel/public/`. **No hace falta copiar ficheros.**

Tras el primer deploy:

```bash
cd laravel
php artisan storage:link
```

Comprueba: `https://presufactura.es/css/app.css` debe mostrar CSS, no HTML.

## 3. Composer y Laravel

```bash
cd laravel
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edita `laravel/.env`. **No commitear `.env`.**

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

```bash
* * * * * cd /home/USUARIO/domains/presufactura.es/public_html/laravel && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Stripe

1. **Checkout** — Pro 12 €/mes recurrente → `STRIPE_PRICE_ID`
2. Claves **live** en `.env`
3. Tras editar `.env`: `php artisan config:cache`
4. **Webhook** — `https://presufactura.es/stripe/webhook`
5. **Customer Portal** activo en Stripe

## 7. Deploy rutinario

```bash
cd ~/domains/presufactura.es/public_html/laravel
./deploy.sh
```

## 8. Permisos

```bash
chmod -R ug+rwx storage bootstrap/cache
```

## Troubleshooting

| Problema | Solución |
|----------|----------|
| 500 en todas las rutas | `laravel/storage/logs/laravel.log`, permisos storage |
| CSS/JS 404 | Actualiza `public_html/.htaccess` (reglas css/js arriba) |
| Logos 404 | `php artisan storage:link` en laravel/ |
| Emails no llegan | SMTP en `.env` |
