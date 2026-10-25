# PresuFactura

Micro-SaaS para autónomos en España: presupuesto → factura → recordatorios de cobro, con Veri*Factu opcional.

Leer `PROJECT.md`, `AGENTS.md` y `FEATURES.md` antes de cambios grandes.

## Skill routing

When the user's request matches an available skill in `.cursor/skills`, read and follow that skill. When in doubt, invoke the skill.

Key routing (skills that exist in this repo):
- Non-trivial choice → `laya`
- Product judgments (route, score, extract, verify) → `jev`
- New page or explicit redesign → `web-design`
- Any HTML table / data grid → `tablas`
- Bug or failing test → `debug`
- Visual check after UI change → `verify`
- Code review of a change → `review`
