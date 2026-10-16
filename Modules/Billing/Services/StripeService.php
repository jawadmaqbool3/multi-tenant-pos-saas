<?php

namespace Modules\Billing\Services;

use App\Exceptions\StripeServiceException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Throwable;

class StripeService
{
    /**
     * Build the SDK client. Keys and API version are read from
     * `config/billing.php`.
     *
     * @return \Stripe\StripeClient
     */
    public function client()
    {
        Stripe::setApiKey(config('billing.secret'));
        Stripe::setApiVersion(config('billing.api_version'));

        return new StripeClient(config('billing.secret'));
    }

    /**
     * Whether a Stripe secret key has been configured.
     *
     * @return bool
     */
    public function isConfigured()
    {
        $secret = config('billing.secret');
        return is_string($secret) && $secret !== '';
    }

    /**
     * Run an SDK call, mapping any Stripe exception to a
     * `StripeServiceException` carrying a machine friendly kind.
     *
     * @param  callable  $callback
     * @return mixed
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function request(callable $callback)
    {
        if (!$this->isConfigured()) {
            throw new StripeServiceException('Stripe is not configured.', 'unconfigured', null, false);
        }

        try {
            return $callback();
        } catch (AuthenticationException $e) {
            throw new StripeServiceException($e->getMessage(), 'authentication_failed', $e, false);
        } catch (RateLimitException $e) {
            throw new StripeServiceException($e->getMessage(), 'rate_limited', $e, true);
        } catch (ApiConnectionException $e) {
            throw new StripeServiceException($e->getMessage(), 'api_unavailable', $e, true);
        } catch (InvalidRequestException $e) {
            $kind = $e->getHttpStatus() === 404 ? 'not_found' : 'invalid_request';
            throw new StripeServiceException($e->getMessage(), $kind, $e, false);
        } catch (ApiErrorException $e) {
            throw new StripeServiceException($e->getMessage(), 'api_unavailable', $e, true);
        } catch (Throwable $e) {
            throw new StripeServiceException($e->getMessage(), 'api_unavailable', $e, false);
        }
    }

    /**
     * Live account balance.
     *
     * @return array{available: array, pending: array}
     */
    public function balance()
    {
        return $this->request(function () {
            $balance = $this->client()->balance->retrieve();
            return [
                'available' => $balance->available,
                'pending' => $balance->pending,
            ];
        });
    }

    /**
     * Create (or return) a Stripe Customer for the given company.
     *
     * @return \Stripe\Customer
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function createCustomer(string $email, string $name, array $metadata = [])
    {
        return $this->request(function () use ($email, $name, $metadata) {
            return $this->client()->customers->create([
                'email' => $email,
                'name' => $name,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Retrieve an existing Stripe Customer.
     *
     * @return \Stripe\Customer
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function retrieveCustomer(string $stripeCustomerId)
    {
        return $this->request(function () use ($stripeCustomerId) {
            return $this->client()->customers->retrieve($stripeCustomerId);
        });
    }

    /**
     * Create a Checkout Session in subscription mode.
     *
     * @return \Stripe\Checkout\Session
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function createCheckoutSession(array $params)
    {
        return $this->request(function () use ($params) {
            return $this->client()->checkout->sessions->create($params);
        });
    }

    /**
     * Retrieve a Checkout Session.
     *
     * @return \Stripe\Checkout\Session
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function retrieveCheckoutSession(string $sessionId, array $expand = [])
    {
        return $this->request(function () use ($sessionId, $expand) {
            $params = $expand ? ['expand' => $expand] : [];
            return $this->client()->checkout->sessions->retrieve($sessionId, $params);
        });
    }

    /**
     * Retrieve a Subscription.
     *
     * @return \Stripe\Subscription
     *
     * @throws \App\Exceptions\StripeServiceException
     */
    public function retrieveSubscription(string $subscriptionId, array $expand = [])
    {
        return $this->request(function () use ($subscriptionId, $expand) {
            $params = $expand ? ['expand' => $expand] : [];
            return $this->client()->subscriptions->retrieve($subscriptionId, $params);
        });
    }
}