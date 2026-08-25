# Verify, complete, motion — PresuFactura

Captured: 2026-08-26 | Branch: main

## Premises (Telegram: auto-accepted, recommended)

1. El catálogo de FEATURES.md ya está implementado. El trabajo es verificar, corregir huecos y pulir.
2. El stack se mantiene: Blade + CSS + jQuery. Sin SPA ni WebGL.
3. Estilo Lusion = motion cinematográfico en marketing; el panel sigue claro para leer importes.
4. Producto 100% gratis. Stripe no se reactiva.

## Scope

- Corregir bugs: show de factura (`paid_at`), etiqueta fiscal en enlace público, recordatorios sin gate de plan.
- Landing/marketing más dinámica (orbes, grain, cursor glow, flujo, marquee, transiciones).
- Panel: entradas suaves, hovers; contraste alto.
- gstack: CLAUDE.md con skill routing.

## Out of scope

- Reactivar paywall, planes Pro, Stripe checkout en UI.
- Rediseñar PDFs (Dompdf no anima).
