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
    RewriteRule ^favicon\.ico$ laravel/public/favicon.ico [L]
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

Recomendado en producción:

```env
LOG_STACK=daily
LOG_LEVEL=warning
SENTRY_LARAVEL_DSN=
```

## 5. Cron (recordatorios)

```bash
* * * * * cd /home/USUARIO/domains/presufactura.es/public_html/laravel && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Stripe (legado, opcional)

La app es gratuita: no hace falta configurar Stripe para producción.
Si quedan variables `STRIPE_*` en `.env`, puedes dejarlas vacías o eliminarlas.
El webhook y Customer Portal solo aplican si hubiera suscripciones antiguas.

## 7. Deploy rutinario

```bash
cd ~/domains/presufactura.es/public_html/laravel
./deploy.sh
```

## 8. Permisos

```bash
chmod -R ug+rwx storage bootstrap/cache
```

## 9. Logs y monitorización

### Revisar logs

Los logs de Laravel están en **`laravel/storage/logs/`**:

| Modo | Fichero |
|------|---------|
| `LOG_STACK=single` (local) | `laravel.log` |
| `LOG_STACK=daily` (producción) | `laravel-YYYY-MM-DD.log` |

Por SSH:

```bash
cd ~/domains/presufactura.es/public_html/laravel
tail -f storage/logs/laravel.log
# o el daily del día:
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

Errores 500, emails fallidos y excepciones del cron aparecen ahí.

### Rotación (Hostinger)

Hostinger **no rota** los logs de la app por ti. Con `LOG_STACK=daily`, Laravel crea un fichero por día y conserva **`LOG_DAILY_DAYS`** (por defecto 14). No hace falta logrotate del sistema salvo que quieras archivar logs antiguos manualmente.

Tras cambiar `LOG_*` en `.env`:

```bash
php artisan config:cache
```

### Health check (cron / uptime)

Comando ligero para comprobar DB, storage y cola. **Exit 0 = OK, exit 1 = fallo.**

```bash
php artisan presufactura:health-check
```

Ejemplo cron cada 15 min (alerta si falla — revisa el log o configura un monitor externo):

```bash
*/15 * * * * cd /home/USUARIO/domains/presufactura.es/public_html/laravel && php artisan presufactura:health-check >> storage/logs/health-check.log 2>&1 || true
```

También puedes usar [UptimeRobot](https://uptimerobot.com) u otro servicio contra `https://presufactura.es/up` (health de Laravel) **y** ejecutar este comando por SSH/cron para validar DB y disco.

### Smoke test (post-deploy)

Tras cada deploy, además del health check:

```bash
php artisan presufactura:smoke-test
```

### Sentry (opcional, sin coste obligatorio)

1. Deja `SENTRY_LARAVEL_DSN=` vacío si no lo usas.
2. Para activarlo: `composer require sentry/sentry-laravel`, crea proyecto en Sentry, pega el DSN en `.env` y `php artisan config:cache`.
3. Sin el paquete instalado, la app ignora el DSN (hook preparado en `AppServiceProvider`).

### Alertas de recordatorios

Si falla `presufactura:process-reminders` (cron horario), se envía un email a **`MAIL_FROM_ADDRESS`** (`facturas@presufactura.es`) y el error queda en el log.

## Troubleshooting

| Problema | Solución |
|----------|----------|
| 500 en todas las rutas | `laravel/storage/logs/laravel.log`, permisos storage |
| CSS/JS 404 | Actualiza `public_html/.htaccess` (reglas css/js arriba) |
| Logos 404 | `php artisan storage:link` en laravel/ |
| Emails no llegan | SMTP en `.env` |
| Veri*Factu pide `php artisan migrate` | SSH/PuTTY: `cd laravel` y `php artisan migrate --force` (o `./deploy.sh`) |

## Veri*Factu

### Qué hacer ahora (Hostinger, PuTTY o Terminal SSH)

No se ejecuta en el administrador de archivos. Entra por **SSH** (hPanel → Avanzado → Acceso SSH, o PuTTY).

```bash
cd ~/domains/presufactura.es/public_html/laravel
php -v
php artisan migrate --force
php artisan config:cache
```

`php -v` debe ser **8.3**. Si no, elige PHP 8.3 en hPanel (Selector PHP) y vuelve a intentar.

Para actualizar código **y** migrar de una vez:

```bash
cd ~/domains/presufactura.es/public_html/laravel
./deploy.sh
```

En hPanel → PHP → Extensiones: activa **soap** y **openssl**.

Después recarga `/configuracion`, marca Veri*Factu y sube el `.p12`. Catálogo demo (solo marcospc1995@gmail.com):

```bash
php artisan presufactura:seed-demo
php artisan presufactura:verifactu-dev-cert
```

### Requisitos PHP

- `ext-openssl` (certificados .p12)
- `ext-soap` (envío AEAT): habilitar en php.ini del servidor

### Variables .env

```
VERIFACTU_ENV=preprod          # preprod | prod
VERIFACTU_MODE=verifactu       # verifactu | no_verifactu
VERIFACTU_SOFTWARE_NIF=        # NIF del productor del software
VERIFACTU_SOFTWARE_NAME=PresuFactura
VERIFACTU_SOFTWARE_VERSION=2.0.0
```

### Certificados

- Cada tenant sube su `.p12` desde `/configuracion` → Veri*Factu.
- Se almacenan cifrados en `storage/app/sif/certs/` (nunca en Git).
- La contraseña del certificado no se persiste; se guarda en caché 24h tras subida.

### Comandos

```bash
php artisan presufactura:verifactu-retry-failed [--user=ID]
php artisan presufactura:verifactu-export {user}
```

### Cola

El envío AEAT usa jobs (`SubmitBillingRecordJob`). Asegurar que el worker de cola está activo (`QUEUE_CONNECTION=database`).
