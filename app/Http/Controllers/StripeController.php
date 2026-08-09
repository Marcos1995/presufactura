<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Throwable;

class StripeController extends Controller
{
    public function __construct(
        private StripeService $stripe,
    ) {}

    public function checkout(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isPro()) {
            return redirect()->route('dashboard')->with('status', 'PresuFactura es gratuito: ya tienes acceso completo.');
        }

        try {
            $session = $this->stripe->createCheckoutSession($user);

            return redirect($session->url);
        } catch (Throwable $e) {
            Log::error('Stripe checkout failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('subscription.index')
                ->with('error', $this->checkoutErrorMessage($e));
        }
    }

    public function success(): View
    {
        return view('stripe.success');
    }

    public function cancel(): View
    {
        return view('stripe.cancel');
    }

    public function webhook(Request $request): Response
    {
        try {
            $this->stripe->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (Throwable) {
            return response('Invalid payload', 400);
        }

        return response('OK', 200);
    }

    private function checkoutErrorMessage(Throwable $e): string
    {
        if ($e instanceof ApiErrorException) {
            return 'Stripe: '.($e->getMessage() ?: 'error al crear la sesión de pago').'. Revisa STRIPE_PRICE_ID live (12 €/mes, recurrente).';
        }

        return $e->getMessage() ?: 'No se pudo iniciar el pago. Revisa la configuración Stripe en el servidor.';
    }
}
