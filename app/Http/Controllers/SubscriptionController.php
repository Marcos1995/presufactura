<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SubscriptionController extends Controller
{
    public function __construct(
        private StripeService $stripe,
    ) {}

    public function index(): View
    {
        return view('subscription.index', ['user' => auth()->user()]);
    }

    public function portal(): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->stripe_customer_id) {
            return redirect()->route('subscription.index')
                ->with('error', 'No tienes una suscripción activa.');
        }

        try {
            $session = $this->stripe->createPortalSession($user);

            return redirect($session->url);
        } catch (Throwable $e) {
            Log::error('Stripe portal failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('subscription.index')
                ->with('error', 'No se pudo abrir el portal de Stripe. '.$e->getMessage());
        }
    }
}
