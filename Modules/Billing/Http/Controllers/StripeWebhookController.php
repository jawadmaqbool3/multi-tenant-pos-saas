<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Services\StripeWebhookService;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    protected $service;

    public function __construct(StripeWebhookService $service)
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

        $result = $this->service->process($event);

        switch ($result) {
            case 'already':
                return response('Already processed.', 200);
            case 'failed':
                return response('Handler failed.', 500);
            default:
                return response('OK', 200);
        }
    }
}
