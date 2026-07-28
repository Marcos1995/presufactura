<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

class StripeService
{
    public function __construct(
        private EmailService $emailService,
    ) {
        $this->bootApi();
    }

    public function createCheckoutSession(User $user): Session
    {
        $this->assertConfigured();

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
            'allow_promotion_codes' => false,
        ];

        if ($user->stripe_customer_id) {
            $params['customer'] = $user->stripe_customer_id;
        } else {
            $params['customer_email'] = $user->email;
        }

        try {
            return Session::create($params);
        } catch (ApiErrorException $e) {
            if ($user->stripe_customer_id && $this->isCustomerError($e)) {
                $user->update([
                    'stripe_customer_id' => null,
                    'stripe_subscription_id' => null,
                ]);

                unset($params['customer']);
                $params['customer_email'] = $user->email;

                return Session::create($params);
            }

            throw $e;
        }
    }

    public function cancelSubscription(User $user): void
    {
        if (! $user->isPro() || ! $user->stripe_subscription_id) {
            return;
        }

        $this->bootApi();

        try {
            Subscription::cancel($user->stripe_subscription_id);
        } catch (ApiErrorException $e) {
            if (! $this->isSubscriptionMissing($e)) {
                throw $e;
            }
        }
    }

    public function createPortalSession(User $user): PortalSession
    {
        $this->assertConfigured();

        if (! $user->stripe_customer_id) {
            throw new RuntimeException('Sin cliente Stripe.');
        }

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
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event->data->object),
            default => null,
        };
    }

    private function bootApi(): void
    {
        $secret = config('services.stripe.secret');
        if ($secret) {
            Stripe::setApiKey($secret);
        }
    }

    private function assertConfigured(): void
    {
        $secret = config('services.stripe.secret');
        $priceId = config('services.stripe.price_id');

        if (empty($secret)) {
            throw new RuntimeException('STRIPE_SECRET no configurado. Ejecuta php artisan config:cache tras editar .env');
        }

        if (empty($priceId) || ! str_starts_with($priceId, 'price_')) {
            throw new RuntimeException('STRIPE_PRICE_ID inválido. Debe ser un price_… de modo live en Stripe Dashboard');
        }

        if (app()->environment('production') && str_starts_with($secret, 'sk_test_')) {
            throw new RuntimeException('STRIPE_SECRET es de test en producción. Usa sk_live_…');
        }
    }

    private function isCustomerError(ApiErrorException $e): bool
    {
        $code = $e->getStripeCode() ?? '';

        return in_array($code, ['resource_missing', 'invalid_request'], true)
            || str_contains(strtolower($e->getMessage()), 'customer');
    }

    private function isSubscriptionMissing(ApiErrorException $e): bool
    {
        $code = $e->getStripeCode() ?? '';

        return $code === 'resource_missing'
            || str_contains(strtolower($e->getMessage()), 'subscription');
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

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        $customerId = $invoice->customer ?? null;
        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();
        if (! $user) {
            return;
        }

        try {
            $this->emailService->sendPaymentFailed($user);
        } catch (\Throwable) {
            // no bloquear webhook
        }
    }
}
