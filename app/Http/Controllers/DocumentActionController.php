<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentActionController extends Controller
{
    public function confirmPaid(Request $request, string $token): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o caducado.');
        }

        $document = Document::where('public_token', $token)
            ->where('type', Document::TYPE_INVOICE)
            ->firstOrFail();

        if ($document->status === Document::STATUS_PAID) {
            return view('public.action-result', [
                'title' => 'Ya estaba marcada como pagada',
                'message' => 'La factura '.$document->number.' ya consta como cobrada.',
            ]);
        }

        if (! in_array($document->status, [Document::STATUS_SENT, Document::STATUS_EXPIRED], true)) {
            return view('public.action-result', [
                'title' => 'Acción no disponible',
                'message' => 'Esta factura no puede marcarse como pagada en su estado actual.',
            ]);
        }

        $document->update([
            'status' => Document::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $document->events()->create([
            'event_type' => DocumentEvent::MARKED_PAID,
            'meta' => ['source' => 'owner_email'],
        ]);

        return view('public.action-result', [
            'title' => 'Factura marcada como cobrada',
            'message' => 'La factura '.$document->number.' se ha marcado como pagada correctamente.',
        ]);
    }
}
