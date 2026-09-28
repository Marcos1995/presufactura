# Graph Report - presufactura  (2026-09-28)

## Corpus Check
- 277 files · ~69,795 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 16, .mdc 3, .css 2)

## Summary
- 1533 nodes · 3070 edges · 188 communities (83 shown, 105 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 76 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `429e0bd5`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Illuminate\Console\Command
- DocumentCalculatorService
- Illuminate\View\View
- User
- Deploy — Hostinger Business (presufactura.es)
- StripeService
- AnalyticsService
- Illuminate\Http\RedirectResponse
- Illuminate\Http\Request
- InvoiceController
- SubmitBillingRecordJob
- Illuminate\Database\Eloquent\Factories\Factory
- Illuminate\Database\Schema\Blueprint
- package.json
- QuoteController
- Company
- Document
- Illuminate\Database\Eloquent\Relations\BelongsTo
- QrService
- BugReportMail
- AeatSoapClient
- EmailService
- Closure
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Eloquent\Relations\HasMany
- SifEvent
- BillingRecordService
- PasswordResetTest.php
- Client
- BillingRecord
- VerifactuProveTest.php
- VerifactuSchema
- DocumentNumberService
- DataExportService
- Prerrequisitos (F1–F8)
- Veri*Factu en Presufactura — Análisis y plan de implementación
- Illuminate\Support\Str
- XmlBuilderService
- Illuminate\Database\Migrations\Migration
- QrServiceTest
- AdminOverviewService
- UserSifConfig
- Illuminate\Support\Facades\Schema
- VerifactuEnv
- Prompts — PresuFactura v1.1
- README.md
- Prompts — PresuFactura Fase Veri*Factu (F9+)
- PresuFactura
- AuthTest
- PdfGeneratorService
- PresuFactura — PROJECT
- DemoCatalogTest
- ClientController
- PublicQuoteController
- composer.json
- FEATURES.md — PresuFactura
- guest.blade.php
- panel.blade.php
- bootstrap/app.php
- require
- require-dev
- 2026_08_04_000001_create_verifactu_tables.php
- Veri*Factu — Entorno de pruebas AEAT (preproducción)
- marketing.blade.php
- config
- Debug
- Checklist de prueba (preprod)
- Illuminate\Database\Eloquent\Factories\HasFactory
- scripts
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- Declaración responsable del software — PresuFactura
- invoice-lines.js
- action-result.blade.php
- public/invoice.blade.php
- PanelPagesWithoutVerifactuTablesTest
- SeoGuidesTest
- psr-4
- logging.php
- Verify (UI)
- Web design
- 0001_01_01_000001_create_cache_table.php
- 2026_07_27_000001_add_onboarding_completed_at_to_users_table.php
- 2026_08_26_000002_add_is_dev_cert_to_user_sif_config.php
- 2026_08_29_000001_add_google_id_to_users_table.php
- Verify, complete, motion — PresuFactura
- SecurityHardeningTest.php
- invoices/show.blade.php
- public/quote.blade.php
- Laya
- Review
- 2024_01_01_000002_create_documents_table.php
- 2024_01_01_000005_create_document_events_table.php
- 2026_09_02_000001_create_bug_reports_table.php
- DatabaseSeeder.php
- deploy.sh script
- PresuFactura — diseño
- landing/index.blade.php
- 404.blade.php
- 500.blade.php
- quotes/show.blade.php
- PresuFactura
- autoload-dev
- extra
- suggest
- help/index.blade.php
- invoices/form.blade.php
- quotes/form.blade.php
- argvinput
- DECISIONES.md
- partials.feedback-prompt
- pdf._styles
- login.blade.php
- register.blade.php
- pdf/invoice.blade.php
- pdf/quote.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 191 edges
2. `Document` - 170 edges
3. `BillingRecord` - 77 edges
4. `TestCase` - 66 edges
5. `Client` - 53 edges
6. `Company` - 45 edges
7. `VerifactuSchema` - 41 edges
8. `AnalyticsService` - 35 edges
9. `InvoiceController` - 31 edges
10. `UserSifConfig` - 31 edges

## Surprising Connections (you probably didn't know these)
- `Cola` --references--> `SubmitBillingRecordJob`  [INFERRED]
  DEPLOY.md → app/Jobs/SubmitBillingRecordJob.php
- `2. Emitir factura de prueba` --references--> `SubmitBillingRecordJob`  [INFERRED]
  docs/VERIFACTU-ENTORNO-PRUEBAS.md → app/Jobs/SubmitBillingRecordJob.php
- `Puntos de enganche (implementados)` --references--> `PdfGeneratorService`  [INFERRED]
  docs/VERIFACTU.md → app/Services/PdfGeneratorService.php
- `Prerrequisitos (F1–F8)` --references--> `ClientController`  [INFERRED]
  docs/VERIFACTU.md → app/Http/Controllers/ClientController.php
- `Prerrequisitos (F1–F8)` --references--> `DocumentActionController`  [INFERRED]
  docs/VERIFACTU.md → app/Http/Controllers/DocumentActionController.php

## Import Cycles
- None detected.

## Communities (188 total, 105 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.05
Nodes (15): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, PHPUnit\Framework\Attributes\Group, AnalyticsTest, AuthorizationTest, ClientInvoiceTest, CompanyIsolationTest, DocumentLimitTest (+7 more)

### Community 1 - "Illuminate\Console\Command"
Cohesion: 0.06
Nodes (16): FunnelReportCommand, HealthCheckCommand, InstallVerifactuDevCertCommand, PrepareTestUserCommand, SetUserPlanCommand, SmokeTestCommand, VerifactuCheckCommand, VerifactuRetryFailedCommand (+8 more)

### Community 2 - "DocumentCalculatorService"
Cohesion: 0.06
Nodes (12): DocumentCalculatorService, HashChainService, Money, Carbon\Carbon, Criterio de done, Decisión previa, Fases, Orden de ejecución (+4 more)

### Community 3 - "Illuminate\View\View"
Cohesion: 0.08
Nodes (15): AuthController, Controller, DashboardController, DocumentActionController, FunnelController, GuideController, HelpController, LandingController (+7 more)

### Community 4 - "User"
Cohesion: 0.08
Nodes (11): {closure#1}(), HasOne, User, ClientPolicy, DocumentPolicy, {closure#1}(), up(), Legado (no expuesto como producto de pago) (+3 more)

### Community 5 - "Deploy — Hostinger Business (presufactura.es)"
Cohesion: 0.06
Nodes (33): AppServiceProvider, 1. Clonar / actualizar código, 2. Assets (CSS/JS), 3. Composer y Laravel, 4. Variables `.env` producción, 5. Cron (recordatorios), 6. Stripe (legado, opcional), 6b. Login con Google (+25 more)

### Community 6 - "StripeService"
Cohesion: 0.10
Nodes (11): StripeController, SubscriptionController, StripeService, RuntimeException, Stripe\BillingPortal\Session, Stripe\Checkout\Session, Stripe\Exception\ApiErrorException, Stripe\Stripe (+3 more)

### Community 7 - "AnalyticsService"
Cohesion: 0.11
Nodes (11): AnalyticsEventController, {closure#1}(), {closure#1}(), AnalyticsEvent, AnalyticsService, DateTimeInterface, Illuminate\Auth\Events\PasswordReset, Illuminate\Http\JsonResponse (+3 more)

### Community 8 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.14
Nodes (6): CompanyController, EmailVerificationController, GoogleAuthController, ProfileController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 9 - "Illuminate\Http\Request"
Cohesion: 0.12
Nodes (18): BugReportController, FeedbackController, OnboardingController, {closure#1}(), {closure#10}(), {closure#2}(), {closure#3}(), {closure#4}() (+10 more)

### Community 10 - "InvoiceController"
Cohesion: 0.18
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), InvoiceController

### Community 11 - "SubmitBillingRecordJob"
Cohesion: 0.16
Nodes (8): Throwable, SubmitBillingRecordJob, VerifactuTransportFactory, VerifactuTransportInterface, Illuminate\Contracts\Queue\ShouldBeUnique, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Queue\Queueable, SubmitBillingRecordJobTest

### Community 12 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.10
Nodes (9): BillingRecordFactory, CompanyFactory, SifEventFactory, static, UserFactory, static, UserSifConfigFactory, Illuminate\Database\Eloquent\Factories\Factory (+1 more)

### Community 13 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.14
Nodes (24): backfill(), {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}() (+16 more)

### Community 14 - "package.json"
Cohesion: 0.08
Nodes (20): devDependencies, autoprefixer, axios, concurrently, laravel-vite-plugin, postcss, tailwindcss, vite (+12 more)

### Community 15 - "QuoteController"
Cohesion: 0.15
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), QuoteController, SitemapController, Illuminate\Http\Response

### Community 16 - "Company"
Cohesion: 0.11
Nodes (4): {closure#1}(), Company, CompanyPolicy, Illuminate\Database\Eloquent\Relations\HasOne

### Community 17 - "Document"
Cohesion: 0.12
Nodes (4): {closure#1}(), Document, {closure#1}(), QuoteInvoiceFlowTest

### Community 18 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.11
Nodes (3): LegalConsent, Payment, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 19 - "QrService"
Cohesion: 0.15
Nodes (6): QrService, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Encoding\Encoding, Endroid\QrCode\ErrorCorrectionLevel, Endroid\QrCode\Writer\PngWriter, Illuminate\Support\Facades\Http

### Community 20 - "BugReportMail"
Cohesion: 0.22
Nodes (9): AccountDeletedMail, BugReportMail, WelcomeMail, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Address, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+1 more)

### Community 21 - "AeatSoapClient"
Cohesion: 0.18
Nodes (4): AeatSoapClient, Closure, SoapClient, AeatSoapClientTest

### Community 22 - "EmailService"
Cohesion: 0.15
Nodes (3): Throwable, ProcessRemindersCommand, EmailService

### Community 23 - "Closure"
Cohesion: 0.19
Nodes (8): CheckDocumentLimit, CheckOnboarding, ForceHttps, RecordHttpErrors, ShareCurrentCompany, Closure, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 24 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.13
Nodes (6): BillingSubmissionAttempt, BugReport, {closure#1}(), LineItem, Reminder, Illuminate\Database\Eloquent\Model

### Community 26 - "SifEvent"
Cohesion: 0.15
Nodes (6): VerifactuExportCommand, SifEvent, {closure#1}(), {closure#2}(), DOMXPath, Illuminate\Support\Facades\Storage

### Community 27 - "BillingRecordService"
Cohesion: 0.18
Nodes (4): VerifactuProveCommand, BillingRecordService, Illuminate\Support\Facades\Queue, BillingRecordServiceTest

### Community 28 - "PasswordResetTest.php"
Cohesion: 0.14
Nodes (8): ResetPasswordNotification, VerifyEmailNotification, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Notifications\Messages\MailMessage, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Password, PasswordResetTest

### Community 30 - "BillingRecord"
Cohesion: 0.16
Nodes (3): BillingRecord, DisabledVerifactuTransport, SandboxVerifactuTransport

### Community 31 - "VerifactuProveTest.php"
Cohesion: 0.12
Nodes (5): Illuminate\Support\Facades\Mail, BugReportTest, {closure#1}(), PublicQuoteTest, VerifactuProveTest

### Community 34 - "DataExportService"
Cohesion: 0.17
Nodes (3): DataExportService, DataExportTest, ZipArchive

### Community 35 - "Prerrequisitos (F1–F8)"
Cohesion: 0.14
Nodes (3): DocumentEvent, Prerrequisitos (F1–F8), Illuminate\Support\Facades\URL

### Community 36 - "Veri*Factu en Presufactura — Análisis y plan de implementación"
Cohesion: 0.14
Nodes (14): Arquitectura recomendada para Presufactura, Decisiones que hay que tomar antes de codificar, ¿Es viable implementarlo en Presufactura?, Estado actual en PresuFactura — verificado en código (F9–F14), Módulo SIF implementado, Orden de implementación sugerido, Pendiente / fuera de alcance v1, Plazos (actualizados con RD-ley 15/2025) (+6 more)

### Community 37 - "Illuminate\Support\Str"
Cohesion: 0.19
Nodes (3): SeedDemoCatalogCommand, VerifactuAeatPreprodCommand, Illuminate\Support\Str

### Community 38 - "XmlBuilderService"
Cohesion: 0.38
Nodes (3): XmlBuilderService, DOMDocument, DOMElement

### Community 39 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 43 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.17
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 45 - "Prompts — PresuFactura v1.1"
Cohesion: 0.17
Nodes (11): 1. Recuperar contraseña, 2. Baja de cuenta (RGPD), 3. Banner de cookies, 4. Verificación de email al registrarse, 5. Tests automáticos (Feature), 6. Monitorización producción (logs + alertas), 7. Favicon, 8. Exportar datos (RGPD) (+3 more)

### Community 46 - "README.md"
Cohesion: 0.20
Nodes (3): Agent rules, Flujo, Think → Simple → Surgical → Verify (Karpathy)

### Community 47 - "Prompts — PresuFactura Fase Veri*Factu (F9+)"
Cohesion: 0.18
Nodes (11): Composer previsto (todas las fases), Criterio de «aplicación completa» fiscalmente, F10 — Hash encadenado y XML, F11 — QR en PDF, F12 — Cliente SOAP AEAT + cola, F13 — UI configuración y anulación, F14 — Cumplimiento, rectificativas y tests, F9 — Migraciones y config base (+3 more)

### Community 48 - "PresuFactura"
Cohesion: 0.18
Nodes (11): Checklist tests manuales, Cron producción, Fases completadas, Modelo gratuito, Notas, PresuFactura, Producción, Requisitos (+3 more)

### Community 50 - "PdfGeneratorService"
Cohesion: 0.27
Nodes (3): PdfGeneratorService, Dompdf\Dompdf, Dompdf\Options

### Community 51 - "PresuFactura — PROJECT"
Cohesion: 0.20
Nodes (10): Comandos utiles, Convenciones, Documentación del repo, Estado, Modelo de producto, Notas de producción, Notas para el agente, PresuFactura — PROJECT (+2 more)

### Community 52 - "DemoCatalogTest"
Cohesion: 0.20
Nodes (3): {closure#1}(), DemoCatalogTest, Blueprint

### Community 55 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 56 - "FEATURES.md — PresuFactura"
Cohesion: 0.22
Nodes (9): Acceso y cuenta, Analítica (sin datos personales), Clientes, Cobros y recordatorios, Facturas, FEATURES.md — PresuFactura, Marketing y legal, Presupuestos (+1 more)

### Community 57 - "guest.blade.php"
Cohesion: 0.22
Nodes (8): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo, partials.bug-report

### Community 58 - "panel.blade.php"
Cohesion: 0.22
Nodes (8): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo, partials.bug-report

### Community 59 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Response, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 60 - "require"
Cohesion: 0.25
Nodes (8): require, dompdf/dompdf, endroid/qr-code, laravel/framework, laravel/socialite, laravel/tinker, php, stripe/stripe-php

### Community 61 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 62 - "2026_08_04_000001_create_verifactu_tables.php"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 63 - "Veri*Factu — Entorno de pruebas AEAT (preproducción)"
Cohesion: 0.25
Nodes (8): Criterios para pasar a producción, Diferencias preprod vs prod, Estrategia recomendada, Pasar a producción, Problemas frecuentes, Referencias, Requisitos previos, Veri*Factu — Entorno de pruebas AEAT (preproducción)

### Community 64 - "marketing.blade.php"
Cohesion: 0.25
Nodes (7): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo

### Community 65 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 66 - "Debug"
Cohesion: 0.29
Nodes (6): 1. Root cause, 2. Compare, 3. Hypothesis, 4. Fix, Debug, Red flags → back to step 1

### Community 67 - "Checklist de prueba (preprod)"
Cohesion: 0.29
Nodes (7): 1. Configurar tenant, 2. Emitir factura de prueba, 3. Validar QR, 4. Probar anulación, 5. Comandos útiles, 6. Test de integración opcional (CI/local con cert), Checklist de prueba (preprod)

### Community 68 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.33
Nodes (3): {closure#1}(), Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Support\Facades\Cache

### Community 69 - "scripts"
Cohesion: 0.33
Nodes (6): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd

### Community 70 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 71 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 72 - "Declaración responsable del software — PresuFactura"
Cohesion: 0.33
Nodes (5): Datos del software, Declaración, Declaración responsable del software — PresuFactura, Notas, Responsable

### Community 73 - "invoice-lines.js"
Cohesion: 0.67
Nodes (5): bindRowEvents(), calcLine(), formatMoney(), reindexRows(), updateTotals()

### Community 74 - "action-result.blade.php"
Cohesion: 0.33
Nodes (5): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo

### Community 75 - "public/invoice.blade.php"
Cohesion: 0.33
Nodes (5): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, partials.verifactu-qr

### Community 78 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 79 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 80 - "Verify (UI)"
Cohesion: 0.40
Nodes (4): 1. Screenshots, 2. Look, 3. Fix and repeat, Verify (UI)

### Community 81 - "Web design"
Cohesion: 0.40
Nodes (4): Before HECHO, Steps, Style = `DESIGN.md`, Web design

### Community 86 - "Verify, complete, motion — PresuFactura"
Cohesion: 0.40
Nodes (4): Out of scope, Premises (Telegram: auto-accepted, recommended), Scope, Verify, complete, motion — PresuFactura

### Community 88 - "invoices/show.blade.php"
Cohesion: 0.40
Nodes (4): invoices._form, layouts.partials.invoice-lines-js, partials.document-totals, partials.verifactu-qr

### Community 89 - "public/quote.blade.php"
Cohesion: 0.40
Nodes (4): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer

### Community 90 - "Laya"
Cohesion: 0.50
Nodes (3): Laya, Reply (decision-only requests), Steps

### Community 91 - "Review"
Cohesion: 0.50
Nodes (3): Check, Do, Review

### Community 96 - "deploy.sh script"
Cohesion: 0.83
Nodes (3): php_bin(), run_composer(), deploy.sh script

### Community 97 - "PresuFactura — diseño"
Cohesion: 0.50
Nodes (3): PresuFactura — diseño, Stitch, Tokens

### Community 98 - "landing/index.blade.php"
Cohesion: 0.50
Nodes (3): partials.landing-product-demo, partials.faq-jsonld, partials.faq-list

### Community 99 - "404.blade.php"
Cohesion: 0.50
Nodes (3): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts

### Community 100 - "500.blade.php"
Cohesion: 0.50
Nodes (3): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts

### Community 101 - "quotes/show.blade.php"
Cohesion: 0.50
Nodes (3): layouts.partials.invoice-lines-js, partials.document-totals, quotes._form

### Community 103 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 104 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 105 - "suggest"
Cohesion: 0.67
Nodes (3): suggest, ext-soap, sentry/sentry-laravel

## Knowledge Gaps
- **239 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+234 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 532 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **105 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `Illuminate\Console\Command`, `StripeService`, `AnalyticsService`, `Illuminate\Http\RedirectResponse`, `SubmitBillingRecordJob`, `Illuminate\Database\Eloquent\Factories\Factory`, `Company`, `Document`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `QrService`, `BugReportMail`, `AeatSoapClient`, `EmailService`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Eloquent\Relations\HasMany`, `SifEvent`, `BillingRecordService`, `PasswordResetTest.php`, `Client`, `VerifactuProveTest.php`, `DocumentNumberService`, `DataExportService`, `Prerrequisitos (F1–F8)`, `Illuminate\Support\Str`, `QrServiceTest`, `AdminOverviewService`, `AuthTest`, `DemoCatalogTest`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `PanelPagesWithoutVerifactuTablesTest`, `SecurityHardeningTest.php`, `DatabaseSeeder.php`?**
  _High betweenness centrality (0.197) - this node is a cross-community bridge._
- **Why does `Document` connect `Document` to `TestCase`, `Illuminate\Console\Command`, `Illuminate\View\View`, `User`, `AnalyticsService`, `InvoiceController`, `SubmitBillingRecordJob`, `QuoteController`, `Company`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `QrService`, `AeatSoapClient`, `EmailService`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Eloquent\Relations\HasMany`, `SifEvent`, `BillingRecordService`, `Client`, `VerifactuProveTest.php`, `VerifactuSchema`, `DocumentNumberService`, `Prerrequisitos (F1–F8)`, `Illuminate\Support\Str`, `XmlBuilderService`, `QrServiceTest`, `AdminOverviewService`, `PdfGeneratorService`, `DemoCatalogTest`, `PublicQuoteController`, `Illuminate\Database\Eloquent\Factories\HasFactory`?**
  _High betweenness centrality (0.168) - this node is a cross-community bridge._
- **Why does `Deploy — Hostinger Business (presufactura.es)` connect `Deploy — Hostinger Business (presufactura.es)` to `PublicQuoteController`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `User` (e.g. with `{closure#1}()` and `Prerrequisitos (F1–F8)`) actually correct?**
  _`User` has 2 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `Document` (e.g. with `{closure#1}()` and `Prerrequisitos (F1–F8)`) actually correct?**
  _`Document` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _239 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.05367231638418079 - nodes in this community are weakly interconnected._