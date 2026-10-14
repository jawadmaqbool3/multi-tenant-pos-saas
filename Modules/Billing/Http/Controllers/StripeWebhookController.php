<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\WebhookEvent;
use Modules\Billing\Services\SubscriptionService;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    protected $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    /**
     * Receive and process Stripe webhook events.
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        $secret = config('billing.webhook_secret');

        if (!$secret) {
            return response('Webhook secret is not configured.', 400);
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            return response('Invalid signature.', 400);
        }

        // Idempotency: only process each event once.
        $existing = WebhookEvent::where('stripe_event_id', $event->id)->first();
        if ($existing && $existing->status === WebhookEvent::STATUS_PROCESSED) {
            return response('Already processed.', 200);
        }

        $record = $existing ?: WebhookEvent::create([
            'stripe_event_id' => $event->id,
            'type' => $event->type,
            'payload' => json_encode($event->toArray()),
            'status' => WebhookEvent::STATUS_RECEIVED,
        ]);

        $record->update(['status' => WebhookEvent::STATUS_PROCESSING]);

        try {
            $this->dispatch($event);
            $record->update(['status' => WebhookEvent::STATUS_PROCESSED]);
        } catch (\Throwable $e) {
            $record->update([
                'status' => WebhookEvent::STATUS_FAILED,
                'failed_at' => now(),
            ]);

            return response('Handler failed.', 500);
        }

        return response('OK', 200);
    }

    /**
     * Route a Stripe event to the matching local handler.
     *
     * @param  \Stripe\Event  $event
     * @return void
     */
    protected function dispatch($event)
    {
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->onCheckoutCompleted($event);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->onSubscriptionChanged($event);
                break;
        }
    }

    /**
     * @param  \Stripe\Event  $event
     * @return void
     */
    protected function onCheckoutCompleted($event)
    {
        $session = $event->data->object;
        $subscriptionId = is_object($session->subscription)
            ? $session->subscription->id
            : $session->subscription;

        if ($subscriptionId) {
            $this->service->syncByStripeId($subscriptionId);
        }
    }

    /**
     * @param  \Stripe\Event  $event
     * @return void
     */
    protected function onSubscriptionChanged($event)
    {
        $subscription = $event->data->object;

        if (!empty($subscription->id)) {
            $this->service->syncByStripeId($subscription->id);
        }
    }
}
