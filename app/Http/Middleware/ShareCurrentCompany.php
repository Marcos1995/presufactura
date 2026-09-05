<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            try {
                $company = $user->currentCompany();
                View::share('currentCompany', $company);
                View::share('userCompanies', $user->companies);
            } catch (\Throwable) {
                View::share('currentCompany', null);
                View::share('userCompanies', collect());
            }
        }

        return $next($request);
    }
}
