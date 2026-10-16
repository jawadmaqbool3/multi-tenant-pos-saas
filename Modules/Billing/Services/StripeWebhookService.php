<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\WebhookEvent;
use Modules\Billing\Repositories\WebhookEventRepository;
use Stripe\Event;

class StripeWebhookService
{
    protected $events;
    protected $subscriptionService;

    public function __construct(WebhookEventRepository $events, SubscriptionService $subscriptionService)
    {
        $this->events = $events;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Process an incoming Stripe event with idempotency.
     * Returns: 'ok', 'already', 'failed'
     *
     * @param  \Stripe\Event  $event
     * @return string
     */
    public function process(Event $event): string
    {
        $existing = $this->events->query()->where('stripe_event_id', $event->id)->first();
        if ($existing && $existing->status === WebhookEvent::STATUS_PROCESSED) {
            return 'already';
        }

        $record = $existing ?: $this->events->create([
            'stripe_event_id' => $event->id,
            'type' => $event->type,
            'payload' => json_encode($event->toArray()),
            'status' => WebhookEvent::STATUS_RECEIVED,
        ]);

        $record = $this->events->query()->where('stripe_event_id', $event->id)->first();
        $this->events->update($record->uid, ['status' => WebhookEvent::STATUS_PROCESSING]);

        try {
            $this->dispatch($event);
            $this->events->update($record->uid, ['status' => WebhookEvent::STATUS_PROCESSED]);
            return 'ok';
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Stripe webhook failed: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'exception' => $e,
            ]);

            $this->events->update($record->uid, [
                'status' => WebhookEvent::STATUS_FAILED,
                'failed_at' => now(),
            ]);
            return 'failed';
        }
    }

    /**
     * Route event to handler.
     *
     * @param  \Stripe\Event  $event
     * @return void
     */
    protected function dispatch($event)
    {
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->subscriptionService->activateFromCheckoutSession($event->data->object->id);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->subscriptionService->syncByStripeId($event->data->object->id);
                break;

            default:
                // Ignore unknown event types.
                break;
        }
    }
}
