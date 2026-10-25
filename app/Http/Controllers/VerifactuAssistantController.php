<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Services\AnalyticsService;
use App\Services\VerifactuAssistant\InvoiceFieldChecker;
use App\Services\VerifactuAssistant\VerifactuQuizEvaluator;
use App\Support\PdfTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifactuAssistantController extends Controller
{
    public function index(AnalyticsService $analytics): View
    {
        $analytics->record('verifactu_quiz_start');

        return view('tools.verifactu.index', [
            'questions' => config('verifactu_quiz.questions'),
            'aeatUrl' => config('verifactu_quiz.aeat_url'),
            'deadlineNote' => config('verifactu_quiz.deadline_note'),
        ]);
    }

    public function quiz(Request $request, VerifactuQuizEvaluator $evaluator, AnalyticsService $analytics): View
    {
        $ids = collect(config('verifactu_quiz.questions'))->pluck('id')->all();
        $rules = [];
        foreach ($ids as $id) {
            $rules[$id] = 'required|string|max:40';
        }
        $answers = $request->validate($rules);
        $result = $evaluator->evaluate($answers);
        $analytics->record('verifactu_quiz_done');

        return view('tools.verifactu.result', [
            'result' => $result,
            'aeatUrl' => config('verifactu_quiz.aeat_url'),
        ]);
    }

    public function checkerForm(): View
    {
        return view('tools.verifactu.checker');
    }

    public function checker(Request $request, InvoiceFieldChecker $checker, AnalyticsService $analytics): View|RedirectResponse
    {
        $data = $request->validate([
            'text' => 'nullable|string|max:50000',
            'pdf' => 'nullable|file|mimes:pdf,txt|max:5120',
        ]);

        $text = trim((string) ($data['text'] ?? ''));
        $tmp = null;

        try {
            if ($request->hasFile('pdf')) {
                $tmp = $request->file('pdf')->getRealPath();
                $extracted = PdfTextExtractor::fromPath((string) $tmp);
                $text = trim($text."\n".$extracted);
            }

            if ($text === '') {
                return back()->with('error', 'Pega el texto de la factura o sube un PDF con texto seleccionable.');
            }

            $result = $checker->check($text);
            $analytics->record('verifactu_pdf_check');

            return view('tools.verifactu.checker-result', [
                'result' => $result,
                'labels' => config('verifactu_quiz.checklist_fields'),
            ]);
        } finally {
            // El fichero temporal de Laravel se elimina al fin del request; no persistimos copia.
        }
    }
}
