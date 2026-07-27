<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentActionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/accion/{token}/cobrada', [DocumentActionController::class, 'confirmPaid'])
    ->name('documents.confirm-paid')
    ->middleware('signed');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/presupuestos', [QuoteController::class, 'index'])->name('quotes.index');

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
    Route::get('/facturas/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/facturas/{invoice}/enviar', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/facturas/{invoice}/pagada', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
});
