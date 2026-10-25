<!-- managed-by-telegram-cursor-bot:agent-kit -->
# Agent rules

## Think → Simple → Surgical → Verify (Karpathy)

1. Di tus supuestos; ante dos lecturas, elige la razonable y dilo (headless: no preguntes).
2. Mínimo código que resuelve lo pedido. Nada especulativo. Si lo simple da lo mismo, lo simple.
3. Toca solo lo necesario, respeta el estilo, limpia solo lo que tu cambio dejó sin uso.
4. Define "hecho" y compruébalo con el check más pequeño.

## Flujo

- Memoria = `PROJECT.md`: léelo primero; si cambia qué hace, stack, comandos o estructura, actualiza `## Estado` en el mismo commit. `graphify-out/` se regenera solo.
- Elección no trivial: skill `laya` (decide el modelo local Laya, queda en `docs/DECISIONES.md`). Sin APIs de pago.
- Juicios en el producto (enrutar, puntuar, extraer, verificar): skill `jev`. Laya local, mismas primitivas que Jev; sin API de TypeSafe.
- Vista: HTML real (normalmente `index.html`). Repo público: `https://<owner>.github.io/<repo>/` en HECHO; `github.com` es el código. Nunca htmlpreview, githack, Vercel, Netlify ni otros hosts de preview.
- Página nueva o rediseño («rehazlo», cambia el estilo): skill `web-design` (el aspecto está solo ahí). Un retoque se edita en el HTML, sin esa skill.
- Cualquier tabla: skill `tablas`. Al pulsar una columna se ordena; el primer clic, de mayor a menor. Una columna fija no deja ver por debajo lo que hace scroll.
- Librería o API externa: mira la versión instalada (lockfile) y su doc oficial antes de usarla. Bug o test roto: skill `debug`.
- Hecho = `git add -A` + commit corto + push. Respuesta: máx. 5 líneas, `HECHO`/`FALLO`, sin relleno.

Kit synced by telegram-cursor-bot
