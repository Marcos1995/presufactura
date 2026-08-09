# AGENTS.md — PresuFactura

Instrucciones para agentes de desarrollo (Cursor u otros).

## Antes de tocar código

1. Leer PROJECT.md, este archivo y `.cursor/rules/`.
2. Respetar stack y notas de producción de PROJECT.md.
3. Preferir minimal diff; no refactors ni features no pedidas.

## Producto

- App 100% gratuita: `User::isPro()` y `User::canCreateDocument()` siempre true.
- No reintroducir paywall, límites Free/Pro ni CTAs de Stripe en UI.
- Stripe puede quedar como código legado (webhooks/servicios); no vender planes.

## Stack y estilo

- Laravel 11 + Blade + jQuery + CSS propio (`public/css/app.css`).
- No añadir React/Vue/Inertia salvo petición explícita.
- Textos de UI en español (es-ES).
- Tests Feature/Unit con PHPUnit (`tests/`).

## Dónde mirar

| Área | Rutas / clases |
|------|----------------|
| Auth / onboarding | `routes/web.php`, `OnboardingController`, middleware `CheckOnboarding` |
| Clientes | `ClientController`, `app/Models/Client.php` |
| Facturas / presupuestos | `InvoiceController`, `QuoteController`, `Document` |
| PDF | views `resources/views/pdf/`, Dompdf |
| Recordatorios | `ProcessRemindersCommand`, schedule en `routes/console.php` o bootstrap |
| Veri*Factu | `app/Services/Verifactu/`, `config/verifactu.php`, docs/ |
| Landing / FAQ | `resources/views/landing/`, `config/faq.php` |

## Verificación

```bash
php artisan test
php artisan presufactura:smoke-test
```

Tras cambios de producto: actualizar FEATURES.md y, si aplica, README/PROJECT.

## Seguridad

- Nunca commitear `.env`, certificados ni secretos.
- No inventar endpoints de pago nuevos.
