<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StripeController extends Controller
{
    public function __construct(
        private StripeService $stripe,
    ) {}

    public function checkout(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isPro()) {
            return redirect()->route('settings.index')->with('status', 'Ya tienes el plan Pro.');
        }

        $session = $this->stripe->createCheckoutSession($user);

        return redirect($session->url);
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
        } catch (\Throwable) {
            return response('Invalid payload', 400);
        }

        return response('OK', 200);
    }
}
