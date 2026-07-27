<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDocumentLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canCreateDocument()) {
            return redirect()
                ->back()
                ->with('error', 'Has alcanzado el límite de 3 documentos al mes del plan Free. Actualiza a Pro para crear más.');
        }

        return $next($request);
    }
}
