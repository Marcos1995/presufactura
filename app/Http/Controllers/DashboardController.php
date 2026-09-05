<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Support\VerifactuSchema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $company = $user->currentCompany();
        $docs = $company->documents();

        $pendingTotal = (clone $docs)
            ->where('type', Document::TYPE_INVOICE)
            ->where('status', Document::STATUS_SENT)
            ->whereDate('due_date', '>=', now()->startOfDay())
            ->sum('total');

        $overdueTotal = (clone $docs)
            ->where('type', Document::TYPE_INVOICE)
            ->whereIn('status', [Document::STATUS_SENT, Document::STATUS_EXPIRED])
            ->whereDate('due_date', '<', now()->startOfDay())
            ->sum('total');

        $collectedMonth = (clone $docs)
            ->where('type', Document::TYPE_INVOICE)
            ->where('status', Document::STATUS_PAID)
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('total');

        $docsThisMonth = $user->documentsThisMonthCount();
        $docsLimit = null;

        $recentDocuments = $company->documents()
            ->with('client')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $showFeedbackPrompt = $recentDocuments->isNotEmpty() && ! $user->feedback()->exists();

        $pendingSif = 0;
        $errorSif = 0;
        if (VerifactuSchema::hasBillingRecordsTable()) {
            $pendingSif = $company->billingRecords()
                ->whereIn('aeat_status', [BillingRecord::STATUS_PENDING, BillingRecord::STATUS_ERROR])
                ->count();
            $errorSif = $company->billingRecords()
                ->where('aeat_status', BillingRecord::STATUS_ERROR)
                ->count();
        }

        $setupIncomplete = ! filled($company->tax_id) || ! filled($company->iban);

        return view('dashboard.index', compact(
            'pendingTotal',
            'overdueTotal',
            'collectedMonth',
            'docsThisMonth',
            'docsLimit',
            'recentDocuments',
            'showFeedbackPrompt',
            'pendingSif',
            'errorSif',
            'setupIncomplete',
            'company',
        ));
    }
}
