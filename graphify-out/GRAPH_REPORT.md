# Graph Report - presufactura  (2026-10-25)

## Corpus Check
- 313 files · ~84,656 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 17, .mdc 3, .css 2)

## Summary
- 1673 nodes · 3310 edges · 217 communities (84 shown, 133 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 78 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `8477c85c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- Illuminate\Console\Command
- DocumentCalculatorService
- Illuminate\View\View
- AuthTest
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
- Client
- Illuminate\Database\Eloquent\Relations\BelongsTo
- QrService
- BugReportMail
- AeatSoapClient
- VerifactuSchema
- Illuminate\Database\Eloquent\Relations\HasMany
- Illuminate\Database\Eloquent\Model
- Document
- Illuminate\Support\Str
- BillingRecord
- ResetPasswordNotification
- ProcessFiscalRemindersCommand.php
- User
- DocumentNumberService
- SmokeTestCommand
- Web design
- Illuminate\Support\Facades\DB
- EmailService.php
- Veri*Factu en Presufactura — Análisis y plan de implementación
- Illuminate\Http\Response
- VerifactuEnv
- Illuminate\Database\Migrations\Migration
- QrServiceTest
- AdminOverviewService
- UserSifConfig
- 2024_01_01_000004_create_reminders_table.php
- DataExportService
- Prompts — PresuFactura v1.1
- README.md
- Prompts — PresuFactura Fase Veri*Factu (F9+)
- PresuFactura
- HashChainService
- BillingRecordService
- PresuFactura — PROJECT
- DemoCatalogTest
- VerifactuProductionCheck
- EmailService
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
- mcp.json
- scripts
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- Declaración responsable del software — PresuFactura
- invoice-lines.js
- action-result.blade.php
- public/invoice.blade.php
- LegalConsent
- InstallVerifactuDevCertCommand.php
- psr-4
- logging.php
- Verify (UI)
- PanelPagesTest
- 0001_01_01_000001_create_cache_table.php
- 2026_07_27_000001_add_onboarding_completed_at_to_users_table.php
- 2026_08_26_000002_add_is_dev_cert_to_user_sif_config.php
- 2026_08_29_000001_add_google_id_to_users_table.php
- Verify, complete, motion — PresuFactura
- ClientController
- invoices/show.blade.php
- public/quote.blade.php
- Laya
- Review
- OnboardingController
- Illuminate\Support\Facades\Schema
- 2026_09_02_000001_create_bug_reports_table.php
- DataExportTest.php
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
- VerifactuProveCommand
- PanelPagesWithoutVerifactuTablesTest
- Dashboard brief (for the Stitch prompt)
- auth-split.blade.php
- IcalBuilder
- HealthCheckCommand
- public-header.blade.php
- AdminOverviewService.php
- console.php
- 2026_08_26_000004_create_user_feedback_table.php
- Build judgments with Laya
- tablas/SKILL.md
- 2026_10_25_000001_add_fiscal_profile_to_companies_table.php

## God Nodes (most connected - your core abstractions)
1. `User` - 194 edges
2. `Document` - 172 edges
3. `BillingRecord` - 77 edges
4. `TestCase` - 66 edges
5. `Company` - 57 edges
6. `Client` - 53 edges
7. `VerifactuSchema` - 41 edges
8. `AnalyticsService` - 39 edges
9. `Controller` - 31 edges
10. `InvoiceController` - 31 edges

## Surprising Connections (you probably didn't know these)
- `Cola` --references--> `SubmitBillingRecordJob`  [INFERRED]
  DEPLOY.md → app/Jobs/SubmitBillingRecordJob.php
- `2. Emitir factura de prueba` --references--> `SubmitBillingRecordJob`  [INFERRED]
  docs/VERIFACTU-ENTORNO-PRUEBAS.md → app/Jobs/SubmitBillingRecordJob.php
- `Prerrequisitos (F1–F8)` --references--> `ClientController`  [INFERRED]
  docs/VERIFACTU.md → app/Http/Controllers/ClientController.php
- `Prerrequisitos (F1–F8)` --references--> `DocumentActionController`  [INFERRED]
  docs/VERIFACTU.md → app/Http/Controllers/DocumentActionController.php
- `Prerrequisitos (F1–F8)` --references--> `InvoiceController`  [INFERRED]
  docs/VERIFACTU.md → app/Http/Controllers/InvoiceController.php

## Import Cycles
- None detected.

## Communities (217 total, 133 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.06
Nodes (17): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Support\Facades\Mail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\View, AuthorizationTest, BugReportTest, ClientInvoiceTest, DocumentLimitTest (+9 more)

### Community 1 - "Illuminate\Console\Command"
Cohesion: 0.17
Nodes (5): FunnelReportCommand, PrepareTestUserCommand, SetUserPlanCommand, VerifactuRetryFailedCommand, Illuminate\Console\Command

### Community 2 - "DocumentCalculatorService"
Cohesion: 0.14
Nodes (4): DocumentCalculatorService, Money, PHPUnit\Framework\TestCase, DocumentCalculatorServiceTest

### Community 3 - "Illuminate\View\View"
Cohesion: 0.07
Nodes (16): AuthController, Controller, DashboardController, DocumentActionController, FunnelController, GuideController, HelpController, LandingController (+8 more)

### Community 5 - "Deploy — Hostinger Business (presufactura.es)"
Cohesion: 0.07
Nodes (29): 1. Clonar / actualizar código, 2. Assets (CSS/JS), 3. Composer y Laravel, 4. Variables `.env` producción, 5. Cron (recordatorios), 6. Stripe (legado, opcional), 6b. Login con Google, 7. Deploy rutinario (+21 more)

### Community 6 - "StripeService"
Cohesion: 0.09
Nodes (16): SubscriptionController, StripeService, Endroid\QrCode\Builder\Builder, Endroid\QrCode\Encoding\Encoding, Endroid\QrCode\ErrorCorrectionLevel, Endroid\QrCode\Writer\PngWriter, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log (+8 more)

### Community 7 - "AnalyticsService"
Cohesion: 0.07
Nodes (14): AnalyticsEventController, {closure#1}(), {closure#1}(), AnalyticsEvent, AnalyticsService, VerifactuQuizEvaluator, DateTimeInterface, Illuminate\Auth\Events\PasswordReset (+6 more)

### Community 8 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.12
Nodes (7): CompanyController, EmailVerificationController, FeedbackController, GoogleAuthController, ProfileController, Illuminate\Foundation\Auth\EmailVerificationRequest, Illuminate\Http\RedirectResponse

### Community 9 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (27): CheckDocumentLimit, CheckOnboarding, ForceHttps, RecordHttpErrors, ShareCurrentCompany, AppServiceProvider, {closure#1}(), {closure#10}() (+19 more)

### Community 10 - "InvoiceController"
Cohesion: 0.18
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), InvoiceController

### Community 11 - "SubmitBillingRecordJob"
Cohesion: 0.11
Nodes (10): Throwable, SubmitBillingRecordJob, DisabledVerifactuTransport, SandboxVerifactuTransport, VerifactuTransportFactory, VerifactuTransportInterface, Illuminate\Contracts\Queue\ShouldBeUnique, Illuminate\Contracts\Queue\ShouldQueue (+2 more)

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
Cohesion: 0.19
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), QuoteController

### Community 16 - "Company"
Cohesion: 0.10
Nodes (4): {closure#1}(), Company, CompanyPolicy, Illuminate\Database\Eloquent\Relations\HasOne

### Community 17 - "Client"
Cohesion: 0.11
Nodes (6): Client, {closure#1}(), ClientPolicy, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Queue, BillingRecordServiceTest

### Community 19 - "QrService"
Cohesion: 0.15
Nodes (3): PublicQuoteController, QrService, Checklist hPanel (no automatizar a ciegas)

### Community 20 - "BugReportMail"
Cohesion: 0.14
Nodes (13): BugReportController, AccountDeletedMail, BugReportMail, FiscalReminderMail, WelcomeMail, BugReport, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable (+5 more)

### Community 21 - "AeatSoapClient"
Cohesion: 0.17
Nodes (5): VerifactuAeatPreprodCommand, AeatSoapClient, Closure, SoapClient, AeatSoapClientTest

### Community 22 - "VerifactuSchema"
Cohesion: 0.16
Nodes (4): HasOne, VerifactuSchema, Dompdf\Dompdf, Dompdf\Options

### Community 24 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.14
Nodes (5): {closure#1}(), BillingSubmissionAttempt, LineItem, Reminder, Illuminate\Database\Eloquent\Model

### Community 25 - "Document"
Cohesion: 0.12
Nodes (4): {closure#1}(), Document, DocumentPolicy, {closure#1}()

### Community 26 - "Illuminate\Support\Str"
Cohesion: 0.19
Nodes (3): FiscalCalendarController, FiscalObligationEngine, Illuminate\Support\Str

### Community 27 - "BillingRecord"
Cohesion: 0.08
Nodes (11): VerifactuExportCommand, BillingRecord, SifEvent, {closure#1}(), {closure#2}(), DOMXPath, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Support\Facades\Cache (+3 more)

### Community 28 - "ResetPasswordNotification"
Cohesion: 0.25
Nodes (5): ResetPasswordNotification, VerifyEmailNotification, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Notifications\Messages\MailMessage

### Community 29 - "ProcessFiscalRemindersCommand.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), ProcessFiscalRemindersCommand, FiscalEstimateService

### Community 30 - "User"
Cohesion: 0.06
Nodes (12): {closure#1}(), HasOne, User, DatabaseSeeder, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable (+4 more)

### Community 33 - "Web design"
Cohesion: 0.25
Nodes (6): Before HECHO, Steps (only case 1 or case 2), Style = `DESIGN.md`, Web design, When Stitch does not run, When Stitch runs

### Community 36 - "Veri*Factu en Presufactura — Análisis y plan de implementación"
Cohesion: 0.18
Nodes (11): Arquitectura recomendada para Presufactura, Decisiones que hay que tomar antes de codificar, ¿Es viable implementarlo en Presufactura?, Orden de implementación sugerido, Pendiente / fuera de alcance v1, Plazos (actualizados con RD-ley 15/2025), Próximo paso concreto, ¿Qué es Veri*Factu? (+3 more)

### Community 37 - "Illuminate\Http\Response"
Cohesion: 0.13
Nodes (5): LetterExplainerController, SitemapController, StripeController, PdfTextExtractor, Illuminate\Http\Response

### Community 39 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 42 - "UserSifConfig"
Cohesion: 0.16
Nodes (3): UserSifConfig, {closure#1}(), up()

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

### Community 49 - "HashChainService"
Cohesion: 0.06
Nodes (14): AiTextService, LetterExplainerService, HashChainService, XmlBuilderService, InvoiceFieldChecker, Carbon\Carbon, Criterio de done, Decisión previa (+6 more)

### Community 50 - "BillingRecordService"
Cohesion: 0.16
Nodes (7): SeedDemoCatalogCommand, PdfGeneratorService, BillingRecordService, Estado actual en PresuFactura — verificado en código (F9–F14), Módulo SIF implementado, Prerrequisitos (F1–F8), Puntos de enganche (implementados)

### Community 51 - "PresuFactura — PROJECT"
Cohesion: 0.20
Nodes (10): Comandos utiles, Convenciones, Documentación del repo, Estado, Modelo de producto, Notas de producción, Notas para el agente, PresuFactura — PROJECT (+2 more)

### Community 52 - "DemoCatalogTest"
Cohesion: 0.20
Nodes (3): {closure#1}(), DemoCatalogTest, Blueprint

### Community 54 - "EmailService"
Cohesion: 0.15
Nodes (3): Throwable, ProcessRemindersCommand, EmailService

### Community 55 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 56 - "FEATURES.md — PresuFactura"
Cohesion: 0.17
Nodes (10): Acceso y cuenta, Analítica (sin datos personales), Clientes, Cobros y recordatorios, Facturas, FEATURES.md — PresuFactura, Legado (no expuesto como producto de pago), Marketing y legal (+2 more)

### Community 57 - "guest.blade.php"
Cohesion: 0.17
Nodes (11): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo, layouts.partials.theme-boot (+3 more)

### Community 58 - "panel.blade.php"
Cohesion: 0.17
Nodes (11): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.logo, layouts.partials.theme-boot (+3 more)

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
Cohesion: 0.20
Nodes (9): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.public-header, layouts.partials.theme-boot (+1 more)

### Community 65 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 66 - "Debug"
Cohesion: 0.29
Nodes (6): 1. Root cause, 2. Compare, 3. Hypothesis, 4. Fix, Debug, Red flags → back to step 1

### Community 67 - "Checklist de prueba (preprod)"
Cohesion: 0.29
Nodes (7): 1. Configurar tenant, 2. Emitir factura de prueba, 3. Validar QR, 4. Probar anulación, 5. Comandos útiles, 6. Test de integración opcional (CI/local con cert), Checklist de prueba (preprod)

### Community 68 - "mcp.json"
Cohesion: 0.24
Nodes (11): github, hostinger-dns, hostinger-domains, hostinger-hosting, ssh, USER_AGENT, cmd, hostinger-dns-mcp (+3 more)

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
Cohesion: 0.22
Nodes (8): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.theme-boot, layouts.partials.theme-switch, layouts.partials.theme-toggle-script, partials.verifactu-qr

### Community 78 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 79 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 80 - "Verify (UI)"
Cohesion: 0.40
Nodes (4): 1. Screenshots, 2. Look, 3. Fix and repeat, Verify (UI)

### Community 86 - "Verify, complete, motion — PresuFactura"
Cohesion: 0.40
Nodes (4): Out of scope, Premises (Telegram: auto-accepted, recommended), Scope, Verify, complete, motion — PresuFactura

### Community 88 - "invoices/show.blade.php"
Cohesion: 0.40
Nodes (4): invoices._form, layouts.partials.invoice-lines-js, partials.document-totals, partials.verifactu-qr

### Community 89 - "public/quote.blade.php"
Cohesion: 0.25
Nodes (7): layouts.partials.app-css, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.legal-footer, layouts.partials.theme-boot, layouts.partials.theme-switch, layouts.partials.theme-toggle-script

### Community 90 - "Laya"
Cohesion: 0.50
Nodes (3): Laya, Reply (decision-only requests), Steps

### Community 91 - "Review"
Cohesion: 0.50
Nodes (3): Check, Do, Review

### Community 93 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.18
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 96 - "deploy.sh script"
Cohesion: 0.83
Nodes (3): php_bin(), run_composer(), deploy.sh script

### Community 98 - "landing/index.blade.php"
Cohesion: 0.20
Nodes (9): layouts.partials.app-css, layouts.partials.app-js, layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.fonts, layouts.partials.public-header, layouts.partials.theme-boot, layouts.partials.theme-toggle-script (+1 more)

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

### Community 190 - "Dashboard brief (for the Stitch prompt)"
Cohesion: 0.33
Nodes (5): Anatomy (always), Charts without libraries (inline SVG, no CDN), CSS, Dashboard brief (for the Stitch prompt), Data honesty (non-negotiable)

### Community 191 - "auth-split.blade.php"
Cohesion: 0.33
Nodes (5): layouts.partials.cookie-banner, layouts.partials.favicon, layouts.partials.theme-boot, layouts.partials.theme-switch, layouts.partials.theme-toggle-script

### Community 196 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 198 - "Build judgments with Laya"
Cohesion: 0.50
Nodes (3): Build judgments with Laya, Call, Design

## Knowledge Gaps
- **278 isolated node(s):** `hostinger-hosting-mcp`, `hostinger-dns-mcp`, `hostinger-domains-mcp`, `@yawlabs/ssh-mcp`, `github` (+273 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 596 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **133 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `Illuminate\Console\Command`, `AuthTest`, `StripeService`, `AnalyticsService`, `Illuminate\Http\RedirectResponse`, `SubmitBillingRecordJob`, `Illuminate\Database\Eloquent\Factories\Factory`, `Company`, `Client`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `BugReportMail`, `AeatSoapClient`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Document`, `BillingRecord`, `ResetPasswordNotification`, `DocumentNumberService`, `Illuminate\Support\Facades\DB`, `QrServiceTest`, `AdminOverviewService`, `UserSifConfig`, `DataExportService`, `BillingRecordService`, `DemoCatalogTest`, `VerifactuProductionCheck`, `EmailService`, `FEATURES.md — PresuFactura`, `VerifactuProveCommand`, `PanelPagesWithoutVerifactuTablesTest`, `AdminOverviewService.php`, `InstallVerifactuDevCertCommand.php`, `PanelPagesTest`, `DataExportTest.php`?**
  _High betweenness centrality (0.178) - this node is a cross-community bridge._
- **Why does `Document` connect `Document` to `TestCase`, `Illuminate\View\View`, `StripeService`, `AnalyticsService`, `InvoiceController`, `SubmitBillingRecordJob`, `QuoteController`, `Company`, `Client`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `QrService`, `AeatSoapClient`, `VerifactuSchema`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Database\Eloquent\Model`, `BillingRecord`, `ProcessFiscalRemindersCommand.php`, `User`, `DocumentNumberService`, `EmailService.php`, `QrServiceTest`, `AdminOverviewService`, `HashChainService`, `BillingRecordService`, `DemoCatalogTest`, `VerifactuProductionCheck`, `EmailService`, `VerifactuProveCommand`, `AdminOverviewService.php`?**
  _High betweenness centrality (0.148) - this node is a cross-community bridge._
- **Why does `BillingRecord` connect `BillingRecord` to `TestCase`, `Illuminate\Console\Command`, `Illuminate\View\View`, `StripeService`, `QrServiceTest`, `SubmitBillingRecordJob`, `Illuminate\Database\Eloquent\Factories\Factory`, `Client`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `BillingRecordService`, `HashChainService`, `AeatSoapClient`, `QrService`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Database\Eloquent\Model`, `DemoCatalogTest`, `User`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `User` (e.g. with `{closure#1}()` and `Prerrequisitos (F1–F8)`) actually correct?**
  _`User` has 2 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `Document` (e.g. with `{closure#1}()` and `Prerrequisitos (F1–F8)`) actually correct?**
  _`Document` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `hostinger-hosting-mcp`, `hostinger-dns-mcp`, `hostinger-domains-mcp` to the rest of the system?**
  _278 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.05552617662612375 - nodes in this community are weakly interconnected._