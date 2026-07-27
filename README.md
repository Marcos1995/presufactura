# PresuFactura

Micro-SaaS para autónomos: presupuesto → factura → recordatorios de cobro.

**Stack:** PHP 8.3, Laravel 11, MySQL, HTML/CSS/jQuery.

## Requisitos

- PHP 8.3+ (extensiones: `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `zip`)
- Composer 2.x
- MySQL 8.0+

## Setup local

### 1. Clonar e instalar dependencias

```bash
cd presufactura
composer install
```

### 2. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales MySQL:

```env
APP_NAME=PresuFactura
APP_URL=http://localhost:8000
DB_DATABASE=presufactura
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 3. Crear base de datos

```sql
CREATE DATABASE presufactura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Migraciones

```bash
php artisan migrate
```

### 5. Servidor de desarrollo

```bash
php artisan serve
```

Abre http://localhost:8000 — registro en `/registro`, login en `/login`.

## Estructura Fase 1

| Componente | Estado |
|------------|--------|
| Migraciones (users, clients, documents, line_items, reminders, document_events) | ✅ |
| Models + relaciones Eloquent | ✅ |
| Auth email/password (registro, login, logout) | ✅ |
| Layout panel con sidebar | ✅ |
| Dashboard, Clientes, Facturas, Presupuestos, Configuración (placeholders) | ✅ |

## Fase 2

| Componente | Estado |
|------------|--------|
| CRUD clientes | ✅ |
| CRUD facturas draft + líneas jQuery | ✅ |
| DocumentCalculatorService | ✅ |
| PdfGeneratorService (Dompdf) | ✅ |
| Middleware límite Free 3 docs/mes | ✅ |

### Rutas Fase 2

| Ruta | Descripción |
|------|-------------|
| `/clientes` | Listado y CRUD clientes |
| `/facturas/nueva` | Crear factura |
| `/facturas/{id}` | Detalle / editar draft |
| `/facturas/{id}/pdf` | Descargar PDF proforma |

## Fase 3

| Componente | Estado |
|------------|--------|
| Estados draft → sent → expired → paid | ✅ |
| DocumentNumberService (FAC-2026-001) | ✅ |
| Enviar factura SMTP + PDF adjunto | ✅ |
| EmailService + templates emails/ | ✅ |
| Recordatorios cron (Pro: +3/+7/+14, autónomo +10) | ✅ |
| Marcar como pagada | ✅ |
| Dashboard KPIs + últimos 10 docs | ✅ |

### Cron producción

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Manual: `php artisan presufactura:process-reminders`

## Rutas panel

| Ruta | Descripción |
|------|-------------|
| `/dashboard` | Dashboard |
| `/clientes` | Clientes |
| `/facturas` | Facturas |
| `/presupuestos` | Presupuestos |
| `/configuracion` | Configuración |

## Próximas fases

- **F2:** CRUD clientes, facturas draft, line items jQuery, PDF Dompdf
- **F3:** Emails SMTP, estados, cron recordatorios, dashboard KPIs
- **F4:** Presupuestos, link público, Stripe Pro, landing

## Notas

- Documentos **proforma** — sin Verifactu v1.
- Plan Free: 3 docs/mes (límite en F4).
- **Nunca** commitear `.env`.

## Cron (producción, Fase 3+)

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```
