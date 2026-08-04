# Prompts — PresuFactura Fase Veri*Factu (F9+)

Copia cada bloque en Cursor Agent. Stack: PHP 8.3, Laravel 11, MySQL. Minimal diff por fase. Leer `docs/VERIFACTU.md` antes de empezar.

**Decisión previa obligatoria:** modalidad **VERI*FACTU** (envío AEAT) vs **NO VERI*FACTU** (conservación + firma XAdES). Recomendado para SaaS: VERI*FACTU + certificado `.p12` por tenant.

**Esfuerzo total estimado:** 4–8 semanas (con librerías) · 8–12 semanas (implementación propia XSD/SOAP).

---

## Resumen: qué falta (0 % hecho hoy)

| # | Bloque | Estado |
|---|--------|--------|
| F9 | Modelo BD + config | ✅ |
| F10 | Hash encadenado + registros alta/anulación | ✅ |
| F11 | QR en PDF | ✅ |
| F12 | Cliente SOAP AEAT + jobs | ✅ |
| F13 | UI certificado + estados envío + anulación | ❌ |
| F14 | Inmutabilidad, rectificativas, legal, tests AEAT | ❌ |

---

## F9 — Migraciones y config base

```
Implementa F9 Veri*Factu en PresuFactura: persistencia SIF.

Requisitos:
- Migraciones: billing_records (document_id, user_id, record_type alta|anulacion, xml_path, hash_current, hash_previous, aeat_status pending|accepted|rejected, aeat_response JSON, sent_at), sif_events (user_id, event_type, payload JSON), verifactu_settings en users o tabla user_sif_config (mode verifactu|no_verifactu, cert_path encrypted, cert_expires_at, software_name/version)
- Modelos: BillingRecord, SifEvent; relaciones Document hasOne BillingRecord, User hasMany
- config/verifactu.php: software id (nombre, versión, NIF productor), URLs QR preprod/prod, WSDL AEAT, entorno
- .env.example: VERIFACTU_ENV=preprod, VERIFACTU_MODE=verifactu, VERIFACTU_SOFTWARE_NIF=, VERIFACTU_SOFTWARE_NAME=PresuFactura
- Sin lógica AEAT aún; factories mínimas si hay tests

Archivos: database/migrations/, app/Models/, config/verifactu.php
```

---

## F10 — Hash encadenado y XML

```
Implementa F10 Veri*Factu: núcleo hash + XML según spec AEAT v1.0.

Requisitos:
- app/Services/Verifactu/HashChainService.php: calcular hash SHA-256 encadenado (orden campos según OM AEAT); getPreviousHash(user_id)
- app/Services/Verifactu/XmlBuilderService.php: construir XML alta desde Document+LineItems+User+Client (campos ROF); stub anulación
- app/Services/Verifactu/BillingRecordService.php: createAltaRecord(Document) en transacción DB; guardar XML en storage/app/sif/{user_id}/
- Hook en InvoiceController::send() tras STATUS_SENT: llamar BillingRecordService (solo facturas, no presupuestos)
- Tests unitarios HashChainService con vectores de la documentación AEAT (fixtures en tests/Fixtures/verifactu/)
- Validación XSD opcional v1 si hay xsd en storage; comentar ruta

Dependencias composer: ninguna externa obligatoria para hash/XML (DOMDocument nativo)
```

---

## F11 — QR en PDF

```
Implementa F11 Veri*Factu: código QR en facturas emitidas.

Requisitos:
- composer require endroid/qr-code (o bacon/bacon-qr-code)
- app/Services/Verifactu/QrService.php: URL QR según modalidad y entorno (params NIF emisor, num factura, fecha, importe)
- PdfGeneratorService: si factura sent/paid y tiene BillingRecord, generar qrDataUri y pasar a vista
- pdf/_document.blade.php: si $qrDataUri, mostrar QR + leyenda «Factura verificable en sede.agenciatributaria.gob.es»; si no, mantener disclaimer proforma
- Badge header: «Factura» vs «Documento proforma» según tenga registro SIF
- Presupuestos sin cambios

Minimal diff en plantilla; CSS en pdf/_styles.blade.php
```

---

## F12 — Cliente SOAP AEAT + cola

```
Implementa F12 Veri*Factu: envío asíncrono a AEAT.

Requisitos:
- Verificar ext-soap en PHP; composer suggest si falta
- app/Services/Verifactu/AeatSoapClient.php: SOAP 1.1 contra WSDL config/verifactu.php; auth certificado .p12 del tenant (openssl_pkcs12_read)
- Job SubmitBillingRecordJob: reintentos 3, backoff; actualizar aeat_status y aeat_response en billing_records
- dispatch job tras createAltaRecord en BillingRecordService
- Comando artisan presufactura:verifactu-retry-failed para reenvíos manuales
- Log estructurado; nunca loguear certificado ni password
- Documentar en DEPLOY.md: ext-soap, almacenamiento certificados cifrado (Laravel encrypt)

Probar solo contra entorno preprod AEAT con certificado de pruebas
```

---

## F13 — UI configuración y anulación

```
Implementa F13 Veri*Factu: UI tenant y flujo anulación.

Requisitos:
- Sección «Veri*Factu» en /configuracion: toggle modalidad, upload .p12 + password (validar caducidad), estado certificado
- Guardar cert cifrado en storage privado (no public); password no persistir
- invoices/show: badge estado AEAT (pendiente/aceptada/rechazada); botón «Anular factura» si sent y aeat accepted
- POST invoices/{id}/anular: crear registro anulación encadenado, job AEAT, status document cancelled (añadir STATUS_CANCELLED a Document si hace falta)
- Bloquear reenvío/editar factura anulada
- FAQ config/faq.php + help/index: párrafo Veri*Factu
- UI es-ES, panel.css existente
```

---

## F14 — Cumplimiento, rectificativas y tests

```
Implementa F14 Veri*Factu: cierre legal y calidad.

Requisitos:
- Facturas rectificativas: campo rectifies_document_id nullable; numeración serie R; XmlBuilder tipo rectificativa
- Inmutabilidad: facturas sent/paid/cancelled no editables ni borrables (403 claro)
- Registro eventos SIF: arranque app (SifEvent), exportación registros (comando presufactura:verifactu-export {user})
- Declaración responsable: plantilla markdown docs/DECLARACION-RESPONSABLE.md (rellenar manual)
- Actualizar legal/terminos.blade.php y README (quitar «sin Verifactu v1» cuando F11+ desplegado)
- Tests Feature: emitir factura → billing_record creado → job fake → QR en PDF
- Tests integración opcional preprod (marcar @group aeatinternal, skip CI)

No implementar Facturae B2B; solo SIF Veri*Factu
```

---

## Orden y dependencias

```
F9 (BD) → F10 (hash/XML) → F11 (QR) → F12 (AEAT) → F13 (UI) → F14 (legal/tests)
```

F11 puede paralelizarse tras F10. F12 requiere certificado de pruebas AEAT.

---

## Composer previsto (todas las fases)

```json
"endroid/qr-code": "^6.0",
"robrichards/xmlseclibs": "^3.1"
```

Extensión PHP: `ext-soap`, `ext-openssl` (ya requerida).

---

## Criterio de «aplicación completa» fiscalmente

- [ ] Factura emitida genera registro SIF con hash encadenado
- [ ] PDF lleva QR verificable
- [ ] Envío AEAT preprod/prod funcional
- [ ] Anulación con registro encadenado
- [ ] Facturas emitidas inmutables
- [ ] Certificado por tenant gestionado de forma segura
- [ ] Declaración responsable firmada y archivada
- [ ] Tests automatizados del hash y flujo emisión

Hasta entonces PresuFactura sigue siendo **proforma** (v1).
