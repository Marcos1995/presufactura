<?php

namespace App\Services;

use App\Models\User;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(User $user): Session
    {
        $customerId = $user->stripe_customer_id;

        $params = [
            'mode' => 'subscription',
            'line_items' => [[
                'price' => config('services.stripe.price_id'),
                'quantity' => 1,
            ]],
            'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel'),
            'metadata' => ['user_id' => (string) $user->id],
            'subscription_data' => [
                'metadata' => ['user_id' => (string) $user->id],
            ],
        ];

        if ($customerId) {
            $params['customer'] = $customerId;
        } else {
            $params['customer_email'] = $user->email;
        }

        return Session::create($params);
    }

    public function createPortalSession(User $user): PortalSession
    {
        return PortalSession::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('subscription.index'),
        ]);
    }

    public function handleWebhook(string $payload, ?string $signature): void
    {
        $event = Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default => null,
        };
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if ($session->mode !== 'subscription') {
            return;
        }

        $userId = $session->metadata->user_id ?? null;
        if (! $userId) {
            return;
        }

        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $user->update([
            'plan' => 'pro',
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => $session->subscription,
            'plan_expires_at' => null,
        ]);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $user = User::where('stripe_subscription_id', $subscription->id)->first();
        if (! $user) {
            return;
        }

        $user->update([
            'plan' => 'free',
            'stripe_subscription_id' => null,
            'plan_expires_at' => now(),
        ]);
    }
}
