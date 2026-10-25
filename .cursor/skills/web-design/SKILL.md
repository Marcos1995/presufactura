---
name: web-design
description: A new page that has no HTML yet, or an explicit redesign ("rediseña", "cambia el estilo", "rehazlo"). Not a typo, a copy edit, or a small visual tweak.
---

# Web design

## When Stitch runs

Call Stitch only in these two cases:

1. The page is new: there is no HTML for it yet (no `index.html`, or whatever file the site actually serves).
2. The user explicitly asks for a redesign, a style change, or to redo it. Examples: "vuelve a hacer el diseño", "cambia el estilo", "rehazlo".

Anything else does not call Stitch. That includes a typo, a data field, a small content edit, and wiring that does not ask for a new design. Edit the existing HTML yourself, the same way the hand-build fallback below does. Do not call `stitch`, `stitch_long`, `generate_screen_from_text`, `edit_screens`, `generate_variants`, or `create_design_system`.

## When Stitch does not run

Change the existing page in place. Do not invent a second visual system. The look stays the `DESIGN.md` paragraph. Then do Before HECHO for what you changed.

## Style = `DESIGN.md`

Missing: create it (≤ 20 lines) with this default plus the user's wishes. This paragraph is the only copy of the look. The Stitch prompt includes it once. Do not restate it.

`Expresivo y verde. Titular display grande (Public Sans, muy negro). La cifra principal va sobre un bloque sólido #18E667 con texto #032612; el botón de la acción principal es #0A3D22 con texto claro. Tarjetas claras, esquinas amplias. Cada sección cambia de gesto: tamaño de tipo, bloque de color o imagen a sangre. Entre una y la siguiente hay transición (el bloque entra, el titular escala); la transición se apaga con prefers-reduced-motion. Una imagen o un motivo decorativo por sección, para que un ranking no sea una lista plana. La fila se lee: nombre, una descripción y miniatura. No repitas el mismo truco en cada bloque. Sin objeto 3D ni WebGL de relleno. Una sola acción principal. Mobile first: el mismo layout nace a 360px y escala a 768 y 1280, sin una pantalla aparte de escritorio. Interruptor sol/luna: una pastilla, el punto se desliza al lado activo (por defecto el del sistema, y se recuerda). No un botón con la palabra Claro u Oscuro; el tema del sistema sin el interruptor no basta. Si hay una ruta o lugares, un solo mapa (OpenStreetMap, sin API de pago, sin mapa de Google embebido). Cada vez que se cita un lugar con web real, el nombre es el enlace a esa web; no inventes la URL. Un nombre que solo es una web enlaza a ella. Sin web, el nombre va al mapa. En un ranking, cada fila tiene Ver mapa (lleva al mapa, resalta el punto y abre el popup) y Ruta (`https://www.google.com/maps/dir/?api=1&destination=<lat>,<lng>`, target=_blank, rel=noopener noreferrer; destino = ese punto; el origen lo elige quien viaja). El popup lo añades al integrar (Stitch no lo dibuja): repite el nombre (enlace si hay web) y Ruta. Esos dos controles, en la fila y en el popup, usan el mismo fondo y el mismo texto que el bloque de la cifra, en claro y en oscuro; si salen grises o con texto blanco, se corrigen. Nada de degradados morados, arcoíris, glassmorphism, emojis como iconos, todo centrado ni plantillas genéricas.`

Keep the Stitch project id, design-system asset id and screen ids here.

Data page (prices, stats, rankings, metrics, panel): also put the anatomy from `dashboard.md` in this folder into the Stitch prompt (hero KPI, comparisons, charts, source line). That file does not repeat the paragraph above.

## Steps (only case 1 or case 2)

1. Reuse the project id in `DESIGN.md`. Missing: `create_project` once, on MCP `stitch` (reads and `create_project` are fast; call them there).
2. Design system once per repo (reuse the asset id). Through `stitch_long`, tool `create_design_system`:
   - `colorMode: LIGHT`, `colorVariant: EXPRESSIVE`, `roundness: ROUND_TWELVE`
   - `customColor`: the accent hex written in the `DESIGN.md` paragraph
   - `bodyFont` and `headlineFont`: `PUBLIC_SANS`
   - `displayName`: the project name
3. Case 1, or case 2 with no screen id yet: `stitch_long` with tool `generate_screen_from_text`. Case 2 when `DESIGN.md` already has the screen id: tool `edit_screens` (that id). Arguments:
   - `projectId`, `prompt`: purpose, real copy from the repo, and the `DESIGN.md` paragraph once. For data, add the `dashboard.md` anatomy. Do not append a second description of the look.
   - `deviceType: MOBILE`, `modelId: GEMINI_3_8_FLASH` (the proxy forces both; on quota or high demand it retries once with `GEMINI_3_5_FLASH_LITE`)
   - `designSystem`: `assets/<id>` from step 2
   - No `DESKTOP`, `TABLET`, or `AGNOSTIC` screen.
4. If `stitch_long` returns `status: running`, call `stitch_wait(job)` until the result. Do not call generate/edit/variants on MCP `stitch` directly: those die at 60 s. Only past 20 min is a failure: report FALLO.
5. `get_screen` on MCP `stitch`. Download `htmlCode.downloadUrl` and publish that file as the page (`index.html`, or the entry the site actually serves). Do not redraw the layout, type, or colors in your own CSS. You may only fix a broken link, replace invented copy with the repo's real text, and add what the `DESIGN.md` paragraph requires and Stitch left out. If the site is generated (`build.py`), port this HTML into the template the build publishes. A second unused page does not count.
6. Write the ids back to `DESIGN.md`.
7. Stitch missing or failing: say so, then hand-build from `DESIGN.md`. Do not invent a second visual system.

## Before HECHO

The `DESIGN.md` paragraph holds. Also 1440px without horizontal scroll, WCAG AA, visible focus, `alt` on images. Data page: the h1 states the finding, charts have a caption, no incomparable numbers ranked. Look with skill `verify`.
