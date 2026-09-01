# Veri*Factu — Entorno de pruebas AEAT (preproducción)

> Hilo dedicado: validar el flujo completo en **preproducción AEAT** antes de pasar a producción.

## Estrategia recomendada

AEAT ofrece dos entornos reales: **preproducción** (`preprod`) y **producción** (`prod`). No existe un sandbox local independiente.

| Fase | Entorno | Cuándo |
|------|---------|--------|
| 1. Validación | `VERIFACTU_ENV=preprod` | Ahora — certificado de pruebas AEAT, facturas de test |
| 2. Producción | `VERIFACTU_ENV=prod` | Tras confirmar alta/anulación/QR aceptados en preprod |

PresuFactura ya apunta por defecto a preprod (`.env.example`). No hace falta un entorno de desarrollo aparte: en local basta con `preprod` + certificado de pruebas.

## Requisitos previos

1. Migraciones: `php artisan migrate`
2. Extensiones PHP: `ext-soap`, `ext-openssl`
3. Cola activa: `QUEUE_CONNECTION=database` + `php artisan queue:work`
4. Variables en `.env`:

```env
VERIFACTU_ENV=preprod
VERIFACTU_SOFTWARE_NIF=          # NIF del productor del software
VERIFACTU_SOFTWARE_NAME=PresuFactura
VERIFACTU_SOFTWARE_VERSION=2.0.0
```

5. Certificado electrónico cualificado del obligado (FNMT persona física o representante). **Hacienda no publica un .p12 descargable.** Preproducción y producción usan el **mismo tipo de certificado**; solo cambia `VERIFACTU_ENV` (URLs `prewww2.aeat.es` ↔ `www2.agenciatributaria.gob.es`). Portal de pruebas: [preportal.aeat.es](https://preportal.aeat.es/). Un `.p12` autofirmado (`php artisan presufactura:verifactu-dev-cert`) cubre XML, hash y QR en el usuario demo, no el SOAP AEAT.

El QR **no lo devuelve Hacienda**: el SIF lo pinta al emitir la factura (arts. 20–21 Orden HAC/1177/2024). El cotejo `ValidarQR` confirma si el registro ya está en AEAT; el ejemplo oficial `nif=89890001K&numserie=12345678-G33&fecha=01-09-2024&importe=241.4` responde `Encontrada` en preprod y en producción.

## Checklist de prueba (preprod)

### 1. Configurar tenant

1. Ir a `/configuracion`
2. Comprobar badge **Entorno de pruebas AEAT** (debe aparecer en amarillo)
3. Activar Veri*Factu, modalidad VERI*FACTU
4. Subir `.p12` de pruebas + contraseña (se cachea 24 h)

### 2. Emitir factura de prueba

1. Crear cliente con NIF válido de test
2. Crear factura en borrador → **Enviar**
3. Verificar:
   - `billing_records.aeat_status` → `pending` y luego `accepted`
   - PDF con QR y badge fiscal
   - Job en cola procesado (`SubmitBillingRecordJob`)

### 3. Validar QR

Escanear el QR del PDF o abrir la URL en:

`https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR`

### 4. Probar anulación

1. Anular la factura enviada
2. Comprobar registro de anulación + envío AEAT aceptado

### 5. Comandos útiles

```bash
# Reintentar envíos fallidos/pendientes
php artisan presufactura:verifactu-retry-failed

# Exportar XMLs del tenant
php artisan presufactura:verifactu-export {user_id}
```

### 6. Test de integración opcional (CI/local con cert)

```env
VERIFACTU_PREPROD_TEST=true
VERIFACTU_TEST_P12_PATH=/ruta/al/certificado_pruebas.p12
VERIFACTU_TEST_P12_PASSWORD=contraseña
```

```bash
php artisan test --group=aeatinternal
php artisan presufactura:verifactu-aeat-preprod
```

## Criterios para pasar a producción

- [ ] Alta aceptada por AEAT preprod (CSV o estado Correcto)
- [ ] QR validado en prewww2.aeat.es
- [ ] Anulación aceptada en preprod
- [ ] Hash encadenado coherente (tests unitarios OK)
- [ ] Declaración responsable preparada (`docs/DECLARACION-RESPONSABLE.md`)

## Pasar a producción

1. Cambiar `.env`: `VERIFACTU_ENV=prod`
2. `php artisan config:clear`
3. Reiniciar workers de cola
4. Cada tenant sube su **certificado real** (.p12 FNMT/electrónico)
5. Comprobar badge **Entorno real AEAT** en `/configuracion`
6. Emitir factura real de prueba y validar QR en `www2.agenciatributaria.gob.es`
7. Archivar declaración responsable firmada ante AEAT

## Diferencias preprod vs prod

| | Preprod | Prod |
|---|---------|------|
| WSDL | `prewww2.aeat.es` | `www2.agenciatributaria.gob.es` |
| QR | `prewww2.aeat.es/.../ValidarQR` | `www2.agenciatributaria.gob.es/.../ValidarQR` |
| Certificado | De pruebas AEAT | Real del obligado |
| Validez fiscal | Solo test | Legal |

## Problemas frecuentes

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| `aeat_status` permanece `pending` | Cola parada o sin contraseña cacheada | `queue:work` + re-guardar certificado con contraseña |
| Error certificado | Contraseña incorrecta o .p12 caducado | Re-subir .p12 vigente |
| SOAP fault | Cert de prod en preprod (o viceversa) | Usar certificado del entorno correcto |
| ext-soap no disponible | PHP sin extensión | Instalar `php-soap` en servidor |

## Referencias

- [AEAT — SIF y VERI*FACTU](https://sede.agenciatributaria.gob.es/Sede/iva/sistemas-informaticos-facturacion-verifactu.html)
- [AEAT desarrolladores](https://www.agenciatributaria.es/AEAT.desarrolladores/Desarrolladores/_menu_/Documentacion/Sistemas_Informaticos_de_Facturacion_y_Sistemas_VERI_FACTU/Sistemas_Informaticos_de_Facturacion_y_Sistemas_VERI_FACTU.html) — QR, hash, WSDL, FAQs
- [Descripción servicios web SOAP](https://sede.agenciatributaria.gob.es/static_files/AEAT_Desarrolladores/EEDD/IVA/VERI-FACTU/Veri-Factu_Descripcion_SWeb.pdf)
- [FAQs desarrolladores](https://sede.agenciatributaria.gob.es/static_files/AEAT_Desarrolladores/EEDD/IVA/VERI-FACTU/FAQs-Desarrolladores.pdf) — facturas de prueba = factura real + QR + anulación posterior
- [Portal de pruebas AEAT](https://preportal.aeat.es/)
- [FNMT — certificado persona física](https://www.sede.fnmt.gob.es/certificados/persona-fisica)
- [Orden HAC/1177/2024](https://www.boe.es/buscar/act.php?id=BOE-A-2024-22138) arts. 20–21: QR 30–40 mm, corrección M, al inicio de la factura
- Análisis general: [`docs/VERIFACTU.md`](VERIFACTU.md)
- Despliegue: [`DEPLOY.md`](../DEPLOY.md) (sección Veri*Factu)
