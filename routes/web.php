<?php

use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentActionController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicQuoteController;
use App\Http\Controllers\QuickStartController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/precios', [PricingController::class, 'index'])->name('pricing');
Route::get('/ayuda', [HelpController::class, 'index'])->name('help');
Route::get('/guias', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guias/{slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/terminos', [LegalController::class, 'terminos'])->name('legal.terminos');
Route::get('/privacidad', [LegalController::class, 'privacidad'])->name('legal.privacidad');
Route::get('/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');
Route::post('/a/e', [AnalyticsEventController::class, 'store'])->middleware('throttle:analytics')->name('analytics.event');

Route::get('/p/{token}', [PublicQuoteController::class, 'show'])->middleware('throttle:public-doc')->name('quotes.public');
Route::post('/p/{token}/aceptar', [PublicQuoteController::class, 'accept'])->middleware('throttle:public-doc')->name('quotes.public.accept');
Route::post('/p/{token}/rechazar', [PublicQuoteController::class, 'reject'])->middleware('throttle:public-doc')->name('quotes.public.reject');
Route::post('/p/{token}/he-pagado', [PublicQuoteController::class, 'claimPaid'])->middleware('throttle:public-doc')->name('invoices.public.claim-paid');

Route::get('/accion/{token}/cobrada', [DocumentActionController::class, 'confirmPaid'])
    ->name('documents.confirm-paid')
    ->middleware('signed');

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

Route::redirect('/register', '/registro', 301);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:login')->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:login')->name('auth.google.callback');
    Route::get('/password/olvidada', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/password/olvidada', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/password/restablecer/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/password/restablecer/{token}', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/email/verificar', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verificar/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verificacion-reenviar', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::middleware('verified')->group(function () {
        Route::get('/onboarding/{step}', [OnboardingController::class, 'show'])->name('onboarding.step');
        Route::post('/onboarding/{step}', [OnboardingController::class, 'store'])->name('onboarding.store');

        Route::middleware('onboarding')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/presupuesto-prueba', [QuickStartController::class, 'sampleQuote'])->middleware('doc.limit')->name('quickstart.quote');
        Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
        Route::post('/aviso-fallo', [BugReportController::class, 'store'])->middleware('throttle:bug-report')->name('bug-reports.store');
        Route::get('/admin', [FunnelController::class, 'index'])->name('admin.index');
        Route::get('/embudo', [FunnelController::class, 'index'])->name('funnel.index');
        Route::get('/configuracion', [ProfileController::class, 'edit'])->name('settings.index');
        Route::put('/configuracion', [ProfileController::class, 'update'])->name('settings.update');
        Route::put('/configuracion/verifactu', [ProfileController::class, 'updateVerifactu'])->name('settings.verifactu.update');
        Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');
        Route::put('/empresas/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::post('/empresas/{company}/activar', [CompanyController::class, 'switch'])->name('companies.switch');
        Route::post('/configuracion/exportar', [ProfileController::class, 'export'])->middleware('throttle:export')->name('settings.export');
        Route::post('/configuracion/eliminar-cuenta', [ProfileController::class, 'destroy'])->name('settings.destroy');
        Route::get('/suscripcion', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('/suscripcion/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');

        Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clientes/nuevo', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clientes', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clientes/{client}/editar', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clientes/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clientes/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::get('/facturas', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/facturas/nueva', [InvoiceController::class, 'create'])->middleware('doc.limit')->name('invoices.create');
        Route::post('/facturas', [InvoiceController::class, 'store'])->middleware('doc.limit')->name('invoices.store');
        Route::get('/facturas/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::put('/facturas/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/facturas/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/facturas/{invoice}/pdf', [InvoiceController::class, 'pdf'])->middleware('throttle:pdf')->name('invoices.pdf');
        Route::post('/facturas/{invoice}/enviar', [InvoiceController::class, 'send'])->middleware('throttle:mail-send')->name('invoices.send');
        Route::post('/facturas/{invoice}/pagada', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('/facturas/{invoice}/anular', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::post('/facturas/{invoice}/rectificativa', [InvoiceController::class, 'createRectificativa'])->middleware('doc.limit')->name('invoices.rectificativa');
        Route::post('/facturas/{invoice}/cobro', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
        Route::post('/facturas/{invoice}/reintentar-verifactu', [InvoiceController::class, 'retryVerifactu'])->name('invoices.verifactu.retry');

        Route::get('/presupuestos', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/presupuestos/nuevo', [QuoteController::class, 'create'])->middleware('doc.limit')->name('quotes.create');
        Route::post('/presupuestos', [QuoteController::class, 'store'])->middleware('doc.limit')->name('quotes.store');
        Route::get('/presupuestos/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::put('/presupuestos/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
        Route::delete('/presupuestos/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
        Route::get('/presupuestos/{quote}/pdf', [QuoteController::class, 'pdf'])->middleware('throttle:pdf')->name('quotes.pdf');
        Route::post('/presupuestos/{quote}/enviar', [QuoteController::class, 'send'])->middleware('throttle:mail-send')->name('quotes.send');
        Route::post('/presupuestos/{quote}/convertir', [QuoteController::class, 'convert'])->middleware('doc.limit')->name('quotes.convert');

        Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
        Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
        });
    });
});
