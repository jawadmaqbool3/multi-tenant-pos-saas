<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class StripeServiceException extends Exception
{
    /**
     * Machine friendly error kind.
     *
     * @var string
     */
    protected $kind;

    /**
     * Whether retrying the request may succeed.
     *
     * @var bool
     */
    protected $isRetryable;

    /**
     * @param  string  $message
     * @param  string  $kind  unconfigured|authentication_failed|invalid_request|
     *                        not_found|rate_limited|api_unavailable
     * @param  \Throwable|null  $previous
     * @param  bool  $isRetryable
     */
    public function __construct($message, $kind = 'api_unavailable', Throwable $previous = null, $isRetryable = false)
    {
        $this->kind = $kind;
        $this->isRetryable = $isRetryable;
        parent::__construct($message, 0, $previous);
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function isRetryable()
    {
        return $this->isRetryable;
    }
}