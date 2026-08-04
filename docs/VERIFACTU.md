# Veri*Factu en Presufactura — Análisis y plan de implementación

> **Estado del proyecto (2026-08-04, F9–F14):** módulo SIF/VERI*FACTU **implementado** (registros encadenados, QR, envío AEAT, anulación, inmutabilidad, rectificativas, export). Sin Veri*Factu activado, los documentos siguen siendo proforma. Pendiente manual: declaración responsable firmada ante AEAT.

**Plan ejecutable por fases:** [`docs/PROMPTS-VERIFACTU.md`](PROMPTS-VERIFACTU.md)

## ¿Qué es Veri*Factu?

**Veri*Factu** (no "Verifacto") es la modalidad del **Reglamento de Sistemas Informáticos de Facturación (RRSIF)**, aprobado por el Real Decreto 1007/2023. Obliga a que el software de facturación:

1. Genere **registros de facturación** (alta y anulación) en formato XML estandarizado.
2. Garantice **integridad e inalterabilidad** mediante hash SHA-256 encadenado.
3. Incluya un **código QR** en todas las facturas (completas y simplificadas).
4. Pueda **remitir** esos registros a la AEAT (modalidad VERI*FACTU) o conservarlos firmados en el emisor (modalidad NO VERI*FACTU).

No sustituye la factura en sí: la factura sigue siendo PDF/papel/electrónica; el registro es un artefacto técnico adicional.

### Plazos (actualizados con RD-ley 15/2025)

| Obligado | Fecha límite |
|----------|--------------|
| Entidades sujetas al Impuesto sobre Sociedades | **1 enero 2027** |
| Resto (autónomos, etc.) | **1 julio 2027** |

2026 es periodo de pruebas: se puede emitir con QR y remitir registros de forma voluntaria.

---

## ¿Es viable implementarlo en Presufactura?

**Sí, es viable**, pero no es un "plugin" de un día. Es un módulo transversal que toca emisión, almacenamiento, PDF y posiblemente despliegue (certificados).

| Enfoque | Esfuerzo estimado | Cuándo tiene sentido |
|---------|-------------------|----------------------|
| **Integrar librería existente** (Python/Node) | 2–4 semanas | Recomendado si el stack encaja |
| **Implementación propia** contra XSD/WSDL AEAT | 6–12 semanas | Control total, más mantenimiento |
| **Solo modalidad NO VERI*FACTU** (conservar + firmar, sin envío automático) | 3–6 semanas | Menos integración AEAT, más responsabilidad en conservación |
| **Usar API de tercero** (Facturae, Holded, etc.) | 1–2 semanas | Si aceptas dependencia externa y coste |

---

## Estado actual en PresuFactura — verificado en código (F9–F14)

### Módulo SIF implementado

| Pieza | Estado | Archivos clave |
|-------|--------|----------------|
| **Persistencia** | ✅ | `billing_records`, `sif_events`, `user_sif_config`, modelos |
| **Hash + XML** | ✅ | `HashChainService`, `XmlBuilderService`, `BillingRecordService` |
| **QR en PDF** | ✅ | `QrService`, `pdf/_document.blade.php` |
| **Envío AEAT** | ✅ | `AeatSoapClient`, `SubmitBillingRecordJob`, comando retry |
| **UI tenant** | ✅ | `/configuracion` Veri*Factu, badges AEAT, anulación |
| **Cumplimiento** | ✅ | Inmutabilidad, rectificativas serie R, export, tests |
| **Declaración responsable** | ⏳ | Plantilla `docs/DECLARACION-RESPONSABLE.md` (firma manual) |
| **Modalidad NO VERI*FACTU** | ❌ | No implementada (solo VERI*FACTU) |
| **Consulta LR / subsanación** | ❌ | Fuera de alcance v1 |

### Prerrequisitos (F1–F8)

| Capa | Qué hay hoy | Archivos clave |
|------|-------------|----------------|
| **Datos fiscales** | NIF, IBAN, dirección, IVA por defecto, prefijos/contadores | `User`, `/configuracion`, onboarding |
| **Clientes** | NIF, dirección, email | `Client`, `ClientController` |
| **Facturación** | CRUD facturas/presupuestos, líneas, totales, numeración | `Document`, `InvoiceController`, `QuoteController`, `DocumentNumberService`, `DocumentCalculatorService` |
| **Estados** | draft → sent → paid / payment_pending; presupuesto accepted | `Document::STATUS_*`, `DocumentActionController` |
| **PDF** | Dompdf; badge fiscal o proforma según registro SIF | `PdfGeneratorService`, `pdf/_document.blade.php` |
| **Trazabilidad** | `DocumentEvent` + `SifEvent` (arranque, export…) | `document_events`, `sif_events` |
| **Multi-tenant** | Cada `User` es un emisor con su cadena de numeración y hashes | `documents.user_id`, `billing_records.user_id` |
| **Cola/jobs** | Envío AEAT asíncrono con reintentos | `SubmitBillingRecordJob` |
| **RGPD v1.1** | Export datos, baja cuenta, verificación email, cookies | según `PROMPTS-V1.1.md` |

### Puntos de enganche (implementados)

| Momento | Implementación |
|---------|----------------|
| Emisión fiscal | `InvoiceController::send()` → `BillingRecordService::createAltaRecord()` |
| PDF | `PdfGeneratorService` → QR data URI + badge fiscal/proforma |
| Inmutabilidad | `InvoiceController::update()` / `destroy()` → 403 en sent/paid/cancelled |
| Anulación | `InvoiceController::cancel()` → registro anulación + job AEAT |
| Config tenant | `ProfileController::updateVerifactu()` — certificado .p12 cifrado |
| Rectificativas | Serie R, `rectifies_document_id`, XML tipo R1 |

---

## Pendiente / fuera de alcance v1

- [ ] **Declaración responsable** firmada y archivada ante AEAT (plantilla en `docs/DECLARACION-RESPONSABLE.md`).
- [ ] Validación XSD estricta antes de envío (opcional).
- [ ] Consulta LR y subsanación automática.
- [ ] Modalidad NO VERI*FACTU (firma XAdES, conservación local).

**Recursos oficiales:**

- WSDL: https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SistemaFacturacion.wsdl
- Documentación: https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html
- PDF técnico v1.0.3: `Veri-Factu_Descripcion_SWeb.pdf` (AEAT Desarrolladores)

---

## Arquitectura recomendada para Presufactura

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Emisión        │────▶│  Módulo SIF      │────▶│  AEAT SOAP      │
│  factura (UI)   │     │  hash + XML + QR │     │  (VERI*FACTU)   │
└─────────────────┘     └────────┬─────────┘     └─────────────────┘
                                   │
                                   ▼
                          ┌──────────────────┐
                          │  BD: registros,  │
                          │  hashes, eventos │
                          └──────────────────┘
                                   │
                                   ▼
                          ┌──────────────────┐
                          │  PDF con QR      │
                          └──────────────────┘
```

### Orden de implementación sugerido

1. **Facturación básica** (clientes, líneas, numeración, IVA) — prerequisito.
2. **Persistencia de registros + hash encadenado** — núcleo SIF.
3. **QR en PDF** — visible para el usuario desde el primer entregable útil.
4. **Cliente SOAP + pruebas en preproducción AEAT**.
5. **Anulaciones, subsanaciones y registro de eventos**.
6. **Declaración responsable y documentación**.

---

## Decisiones que hay que tomar antes de codificar

1. **Modalidad:** ¿VERI*FACTU (envío automático) o NO VERI*FACTU (conservación + firma)?
2. **Stack:** ¿Python (zeep/lxml), Node, .NET? Debe soportar SOAP + XML + certificados.
3. **Certificados:** ¿Cada cliente aporta el suyo o gestionáis un HSM/servicio centralizado?
4. **Multi-tenant:** Si Presufactura es SaaS, cada tenant necesita su certificado y su cadena de hashes.
5. **¿Librería o propio?** Buscar mantenidas en PyPI/npm antes de reimplementar el hash/XML.

---

## Riesgos y limitaciones

- **Errores en el hash** invalidan toda la cadena; hay que seguir la spec al byte.
- **Certificados caducados** bloquean el envío; hay que monitorizar vencimiento.
- **Cambios de XSD** de AEAT exigen actualizar el software periódicamente.
- **Sanciones** por software no conforme: hasta 50.000 € por ejercicio (LGT).
- **No es factura electrónica B2B** (Facturae); Veri*Factu es complementario.

---

## Próximo paso concreto

1. Completar y firmar la **declaración responsable** (`docs/DECLARACION-RESPONSABLE.md`).
2. Probar flujo completo en **preproducción AEAT** con certificado de pruebas por tenant.
3. Configurar `VERIFACTU_ENV=prod` y monitorizar caducidad de certificados en producción.

---

## Referencias

- [AEAT — SIF y VERI*FACTU](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html)
- [Cuestiones generales](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/cuestiones-generales.html)
- [WSDL servicios web](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/informacion-tecnica/wsdl-servicios-web.html)
- RD 1007/2023 — Reglamento RRSIF
- RD-ley 15/2025 — Ampliación plazos a 2027
