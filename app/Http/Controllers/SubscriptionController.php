<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

        $session = $this->stripe->createPortalSession($user);

        return redirect($session->url);
    }
}
