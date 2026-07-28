# Prompts — PresuFactura v1.1

Copia cada bloque en Cursor Agent. Stack: PHP 8.3, Laravel 11, MySQL, HTML/CSS/jQuery, es-ES. Minimal diff. Sin Verifactu.

---

## 1. Recuperar contraseña

```
Implementa recuperación de contraseña en PresuFactura (Laravel 11).

Requisitos:
- Rutas: GET/POST /password/olvidada, GET/POST /password/restablecer/{token}
- UI es-ES en layouts/guest.blade.php (mismo estilo que login/registro)
- Enlace «¿Olvidaste tu contraseña?» en auth/login.blade.php
- Usar Laravel Password Reset nativo (notifications o Mail existente)
- Email branded con emails/layout.blade.php si encaja; asunto en español
- Token expira según config auth; throttle en rutas
- Tras reset exitoso → redirect login con mensaje flash
- No añadir paquetes externos
- Tests Feature mínimos: solicitud email + reset con token válido

Archivos probables: routes/web.php, AuthController o ForgotPasswordController, views auth/forgot-password.blade.php, auth/reset-password.blade.php, User model (Notifiable), config/auth.php si hace falta.
```

---

## 2. Baja de cuenta (RGPD)

```
Implementa baja de cuenta en PresuFactura.

Requisitos:
- Sección «Zona peligrosa» en /configuracion (settings)
- Botón «Eliminar mi cuenta» → modal confirmación (escribir email o «ELIMINAR»)
- POST /configuracion/eliminar-cuenta — solo usuario autenticado
- Borrar en cascada o soft-delete: user, clients, documents, line_items, reminders, document_events, logo en storage
- Cancelar suscripción Stripe si user isPro() antes de borrar (StripeService)
- Logout + redirect landing con flash «Cuenta eliminada»
- Email confirmación opcional a facturas@ o al email del user
- UI es-ES; coherente con panel.css existente
- Alineado con privacidad.blade.php (baja en 30 días → aquí es inmediata)

Minimal diff. Sin refactor de modelos no necesario.
```

---

## 3. Banner de cookies

```
Implementa banner de consentimiento de cookies en PresuFactura.

Requisitos:
- Banner fijo abajo en layouts: marketing, guest, panel (partial layouts/partials/cookie-banner.blade.php)
- Texto es-ES breve + enlace a route('legal.cookies') + botones «Aceptar» / «Solo necesarias»
- Guardar preferencia en localStorage (cookie_consent=accepted|essential); no banner si ya elegido
- CSS en public/css/app.css (clases .cookie-banner, minimal, no librerías)
- Sin cookies de analytics de terceros por ahora — solo aviso legal RGPD básico
- No bloquear la app si no aceptan (informative + accept)
- jQuery OK si ya está en panel; vanilla en marketing/guest

Incluir en legal/cookies.blade.php tipos de cookies usadas (sesión Laravel, CSRF).
```

---

## 4. Verificación de email al registrarse

```
Implementa verificación de email obligatoria tras registro en PresuFactura.

Requisitos:
- User implements MustVerifyEmail
- Tras register → redirect a /email/verificar (guest layout) «Revisa tu bandeja»
- Middleware verified en rutas panel (dashboard, clientes, facturas…) excepto logout y página verificación
- Reenviar email: POST /email/verificacion-reenviar con throttle
- Email WelcomeMail puede enviarse después de verificar, o incluir link verificación — elige lo más simple
- UI es-ES; rutas en español (/email/verificar)
- Usar notification VerifyEmail de Laravel customizada en español
- En local .env MAIL_MAILER=log debe funcionar

Minimal diff. No romper onboarding: verificar email ANTES de onboarding o después — recomienda después de verificar email, antes onboarding.
```

---

## 5. Tests automáticos (Feature)

```
Añade tests Feature PHPUnit para flujos críticos de PresuFactura.

Cubrir al menos:
- Registro + login
- Crear cliente + factura borrador (auth user factory)
- Límite Free 3 docs/mes (CheckDocumentLimit middleware)
- Presupuesto aceptado vía POST /p/{token}/aceptar
- Smoke: rutas landing, precios, ayuda responden 200

Usar RefreshDatabase, UserFactory, Mail::fake(), sin Stripe real.
phpunit.xml ya existe. Ejecutar php artisan test y que pasen.
No tests triviales de ExampleTest — reemplazar o ampliar.
```

---

## 6. Monitorización producción (logs + alertas)

```
Mejora observabilidad en producción PresuFactura sin servicios de pago obligatorios.

Requisitos:
- Documentar en DEPLOY.md: revisar laravel/storage/logs/laravel.log, rotación Hostinger
- Config logging.php: daily channel en production si no está
- Comando artisan presufactura:health-check (opcional): DB ping, disk storage writable, queue — exit 1 si falla (para cron externo u uptime)
- Preparar hook opcional Sentry: comentario en .env.example SENTRY_LARAVEL_DSN= vacío; AppServiceProvider reportable solo si DSN presente — NO instalar sentry/sdk salvo que añadas como suggest en composer
- Email a admin si presufactura:process-reminders falla — solo si encaja en un try/catch existente, minimal

No over-engineering. Documentación clara para Hostinger.
```

---

## 7. Favicon

```
Añade favicon a todas las páginas PresuFactura.

Requisitos:
- Usar public/favicon.ico existente
- Añadir en <head> de layouts/marketing.blade.php, layouts/guest.blade.php, layouts/panel.blade.php, emails/layout (si aplica), public/invoice.blade.php, public/quote.blade.php
- asset('favicon.ico') — en Hostinger se sirve vía .htaccess o copiar a raíz; documentar si hace falta regla RewriteRule ^favicon.ico$
- apple-touch-icon opcional si hay logo SVG

Minimal diff, solo tags link rel icon.
```

---

## 8. Exportar datos (RGPD)

```
Implementa exportación de datos personales RGPD en PresuFactura.

Requisitos:
- Botón «Descargar mis datos» en /configuracion
- GET o POST /configuracion/exportar → genera ZIP descargable
- Contenido ZIP: profile.json (user sin password), clients.json, documents.json con line_items, events básicos
- Sin librerías pesadas — ZipArchive PHP nativo
- Nombre fichero: presufactura-datos-{Y-m-d}.zip
- Rate limit: 1 export cada 24h por user (cache key)
- UI es-ES, mensaje «Preparando exportación…»
- Log en document_events o storage/logs quién exportó (opcional)

Minimal diff. No incluir PDFs binarios en ZIP v1 — solo JSON.
```

---

## Orden recomendado

1. Favicon (5 min)
2. Recuperar contraseña
3. Banner cookies
4. Baja de cuenta + exportar datos (RGPD junto)
5. Verificación email
6. Tests Feature
7. Monitorización

---

## Verifactu — NO implementar aún

Guardar para fase futura. Prompt stub:

```
[FUTURO — NO EJECUTAR AHORA] Diseña fase Verifactu para PresuFactura: facturas fiscales inmutables, QR AEAT, registro hash, certificado digital usuario, series numeración legal, rectificativas. Proforma actual se mantiene en paralelo. Requiere análisis legal + API AEAT. Solo plan en markdown, sin código.
```
