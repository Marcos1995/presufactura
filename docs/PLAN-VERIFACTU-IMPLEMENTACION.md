# Plan de implementación Veri*Factu — PresuFactura

> Generado 2026-08-04. Ejecución fase a fase F9→F14.

## Decisión previa

| Decisión | Valor |
|----------|-------|
| Modalidad | **VERI*FACTU** (envío AEAT) |
| Certificado | `.p12` por tenant, cifrado en storage privado |
| Stack | PHP nativo (DOMDocument, ext-soap, endroid/qr-code) |

## Fases

| Fase | Entregable | Dependencia |
|------|------------|-------------|
| **F9** | `billing_records`, `sif_events`, `user_sif_config`, modelos, `config/verifactu.php` | — |
| **F10** | `HashChainService`, `XmlBuilderService`, `BillingRecordService`, hook en `InvoiceController::send()` | F9 |
| **F11** | `QrService`, QR en PDF, badge fiscal vs proforma | F10 |
| **F12** | `AeatSoapClient`, `SubmitBillingRecordJob`, comando retry | F10, cert preprod |
| **F13** | UI `/configuracion` Veri*Factu, anulación, badge AEAT | F9–F12 |
| **F14** | Inmutabilidad, rectificativas, export, legal, tests | F9–F13 |

## Orden de ejecución

```
F9 → F10 → F11 → F12 → F13 → F14
         ↘ F11 puede empezar tras F10
```

## Criterio de done

- [x] Plan documentado
- [ ] Factura enviada → registro SIF + hash encadenado
- [ ] PDF con QR verificable
- [ ] Job AEAT (preprod)
- [ ] Anulación encadenada
- [ ] Facturas emitidas inmutables
- [ ] Tests hash + flujo emisión
