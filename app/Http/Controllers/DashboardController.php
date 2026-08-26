<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $pendingTotal = $user->documents()
            ->where('type', Document::TYPE_INVOICE)
            ->where('status', Document::STATUS_SENT)
            ->whereDate('due_date', '>=', now()->startOfDay())
            ->sum('total');

        $overdueTotal = $user->documents()
            ->where('type', Document::TYPE_INVOICE)
            ->whereIn('status', [Document::STATUS_SENT, Document::STATUS_EXPIRED])
            ->whereDate('due_date', '<', now()->startOfDay())
            ->sum('total');

        $collectedMonth = $user->documents()
            ->where('type', Document::TYPE_INVOICE)
            ->where('status', Document::STATUS_PAID)
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('total');

        $docsThisMonth = $user->documentsThisMonthCount();
        $docsLimit = null;

        $recentDocuments = $user->documents()
            ->with('client')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $showFeedbackPrompt = $recentDocuments->isNotEmpty() && ! $user->feedback()->exists();

        return view('dashboard.index', compact(
            'pendingTotal',
            'overdueTotal',
            'collectedMonth',
            'docsThisMonth',
            'docsLimit',
            'recentDocuments',
            'showFeedbackPrompt',
        ));
    }
}
