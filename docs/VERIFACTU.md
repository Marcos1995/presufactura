# Veri*Factu en Presufactura — Análisis y plan de implementación

> **Estado del proyecto:** la aplicación Laravel ya gestiona presupuestos y facturas; la integración SIF/VERI*FACTU sigue pendiente. Este documento describe qué falta y cómo implementarlo.

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

## Estado actual en PresuFactura (v1)

### Ya existe (prerrequisitos cumplidos)

| Capa | Qué hay hoy |
|------|-------------|
| **Datos fiscales** | `User`: NIF (`tax_id`), IBAN, dirección, IVA por defecto |
| **Facturación** | CRUD facturas/presupuestos, líneas, totales, numeración (`DocumentNumberService`) |
| **PDF** | `PdfGeneratorService` + plantillas Blade (`pdf/invoice.blade.php`) |
| **Trazabilidad básica** | `DocumentEvent` (created, sent, paid…) — **no** eventos SIF |
| **Multi-tenant** | Cada `User` es un emisor independiente |

### Lo que falta (módulo SIF completo)

| Pieza | Archivos / dependencias a crear |
|-------|----------------------------------|
| **Migraciones** | `billing_records`, `billing_record_hashes`, `sif_events`, certificado por tenant |
| **Servicios PHP** | `app/Services/Verifactu/HashChainService`, `XmlBuilderService`, `QrService`, `AeatSoapClient` |
| **Jobs/cola** | Envío asíncrono a AEAT con reintentos |
| **Config** | `config/verifactu.php` + vars `.env` (entorno preprod/prod, modalidad) |
| **Composer** | Cliente SOAP (`ext-soap` o `php-soap/wsdl`), generador QR (`endroid/qr-code` o similar), validación XSD |
| **UI** | Subida certificado `.p12`, toggle modalidad, estado envío AEAT, anulación |
| **PDF** | QR incrustado + leyenda "Factura verificable" (sustituir disclaimer proforma) |
| **Inmutabilidad** | Bloquear edición/borrado de facturas emitidas; solo anulación con registro encadenado |
| **Legal** | Declaración responsable AEAT, actualizar términos/FAQ |
| **Tests** | Unitarios hash/XML + integración entorno pruebas AEAT |

**Dependencias Composer sugeridas:** `ext-soap`, `endroid/qr-code`, `robrichards/xmlseclibs` (firma XAdES si modalidad NO VERI*FACTU).

---

## Qué falta hoy en Presufactura (detalle por capas)

**Falta todo el módulo SIF**; la facturación proforma actual no es conforme. Resumen:

### 1. Modelo de datos (base)

- [ ] Identificación del **SIF**: nombre, versión, NIF del productor, tipo de uso.
- [ ] **Registro de facturación de alta** por cada factura emitida.
- [ ] **Registro de anulación** por cada factura anulada.
- [ ] **Cadena de hashes**: guardar hash actual + hash del registro anterior (por emisor/NIF).
- [ ] **Registro de eventos** del sistema (arranque, parada, exportaciones, incidencias).
- [ ] Campos obligatorios ROF: NIF emisor/receptor, número, fecha, base, cuota, tipo impositivo, etc.

### 2. Lógica de negocio

- [ ] Generación del **hash encadenado** según especificación OM (orden y formato de campos estricto).
- [ ] Construcción del **XML** conforme a `SuministroLR.xsd`.
- [ ] Validación interna contra XSD antes de enviar o imprimir.
- [ ] Flujos de **subsanación** y **reintento** ante rechazo AEAT.
- [ ] **Anulación** con su propio registro encadenado.
- [ ] Impedir modificar/borrar facturas sin dejar traza (solo anulación + nuevo registro).

### 3. QR en la factura (PDF/Papel)

- [ ] Generar URL del QR con parámetros exigidos por AEAT.
- [ ] Incrustar QR en plantilla PDF (y opcional leyenda "Factura verificable").
- [ ] Diferenciar URL según modalidad VERI*FACTU vs NO VERI*FACTU.

### 4. Integración AEAT (modalidad VERI*FACTU)

- [ ] Cliente **SOAP 1.1** document/literal contra WSDL oficial.
- [ ] Autenticación con **certificado electrónico cualificado** (FNMT, Camerfirma, etc.) del obligado tributario.
- [ ] Entornos **preproducción** y **producción** configurables.
- [ ] Cola de envío, reintentos y registro de respuestas (aceptación/rechazo).
- [ ] Consulta de registros presentados (`ConsultaLR.xsd`).

**Recursos oficiales:**

- WSDL: https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SistemaFacturacion.wsdl
- Documentación: https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html
- PDF técnico v1.0.3: `Veri-Factu_Descripcion_SWeb.pdf` (AEAT Desarrolladores)

### 5. Modalidad NO VERI*FACTU (alternativa)

Si no se remite en tiempo real:

- [ ] **Firma electrónica** de cada registro (XAdES) con certificado del sistema.
- [ ] **Registro de eventos** con la misma seguridad.
- [ ] Exportación bajo requerimiento AEAT (remisión bajo requerimiento).
- [ ] QR con URL de comunicación (no verificación directa).

### 6. Cumplimiento legal del fabricante

- [ ] **Declaración responsable** del software ante AEAT (modelo oficial).
- [ ] Documentación de usuario: modalidad elegida, trazabilidad, exportación.
- [ ] Política de actualizaciones cuando AEAT publique nuevas versiones XSD.

### 7. Infraestructura y operación

- [ ] Almacenamiento seguro de certificados (.p12) — nunca en git.
- [ ] Backup de registros mínimo **4 años**.
- [ ] Logs de auditoría inmutables.
- [ ] Tests automatizados contra entorno de pruebas AEAT.

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

1. Decidir modalidad (VERI*FACTU recomendada para SaaS) y certificado por tenant.
2. Migraciones + modelos `BillingRecord`, `SifEvent`.
3. Crear `app/Services/Verifactu/` con: `HashChainService`, `XmlBuilderService`, `QrService`, `AeatSoapClient`.
4. Hook en `InvoiceController` al emitir (status `sent`): generar registro, hash, QR, encolar envío AEAT.
5. Probar una factura de alta en **entorno de pruebas AEAT** antes de producción.

---

## Referencias

- [AEAT — SIF y VERI*FACTU](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html)
- [Cuestiones generales](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/cuestiones-generales.html)
- [WSDL servicios web](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu/informacion-tecnica/wsdl-servicios-web.html)
- RD 1007/2023 — Reglamento RRSIF
- RD-ley 15/2025 — Ampliación plazos a 2027
