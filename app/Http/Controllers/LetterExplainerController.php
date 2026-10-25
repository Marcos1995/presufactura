<?php

namespace App\Http\Controllers;

use App\Services\Fiscal\IcalBuilder;
use App\Services\LetterExplainer\LetterExplainerService;
use App\Support\PdfTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LetterExplainerController extends Controller
{
    public function index(): View
    {
        return view('tools.cartas.index');
    }

    public function explain(Request $request, LetterExplainerService $explainer): View
    {
        $data = $request->validate([
            'text' => 'nullable|string|max:50000',
            'file' => 'nullable|file|mimes:pdf,txt,jpg,jpeg,png,webp|max:5120',
        ]);

        $text = trim((string) ($data['text'] ?? ''));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $mime = (string) $file->getMimeType();
            if (str_contains($mime, 'pdf') || $file->getClientOriginalExtension() === 'pdf') {
                $text = trim($text."\n".PdfTextExtractor::fromPath((string) $path));
            } elseif (str_starts_with($mime, 'text/') || $file->getClientOriginalExtension() === 'txt') {
                $text = trim($text."\n".(string) file_get_contents((string) $path));
            } else {
                // Imagen: sin OCR local; pedimos pegar texto. Evitamos guardar la imagen.
                if ($text === '') {
                    return view('tools.cartas.index', [
                        'error' => 'Para fotos, pega también el texto de la carta (o usa un PDF con texto). No guardamos el archivo.',
                    ]);
                }
            }
        }

        if ($text === '') {
            return view('tools.cartas.index', [
                'error' => 'Pega el texto de la notificación o sube un PDF/TXT.',
            ]);
        }

        $result = $explainer->explain($text);
        // No se persiste $text ni el fichero.

        return view('tools.cartas.result', compact('result'));
    }

    public function ics(Request $request, IcalBuilder $ical): Response
    {
        $data = $request->validate([
            'title' => 'required|string|max:180',
            'date' => 'required|date_format:Y-m-d',
            'url' => 'nullable|url|max:300',
        ]);

        $body = $ical->singleDay(
            $data['title'],
            $data['date'],
            'Plazo orientativo — PresuFactura',
            $data['url'] ?? null
        );

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="plazo-presufactura.ics"',
        ]);
    }
}
