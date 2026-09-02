<?php

namespace App\Http\Controllers;

use App\Mail\BugReportMail;
use App\Models\BugReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class BugReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('bug_reports')) {
            return back()->with('error', 'El aviso de fallos aún no está disponible. Ejecuta las migraciones.');
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:4000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $url = (string) ($data['page_url'] ?? '');
        if ($url === '' || ! str_starts_with($url, config('app.url'))) {
            $url = url()->previous() ?: url('/dashboard');
        }

        $report = BugReport::create([
            'user_id' => $request->user()->id,
            'page_url' => mb_substr($url, 0, 500),
            'description' => $data['description'],
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
        ]);

        try {
            Mail::to((string) config('mail.support_address'))->send(new BugReportMail($report));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el aviso de fallo por email', [
                'bug_report_id' => $report->id,
                'message' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Aviso enviado a facturas@presufactura.es. Gracias.');
    }
}
