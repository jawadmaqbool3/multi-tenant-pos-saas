<?php

namespace App\Exceptions;

class PlanLimitExceededException extends CustomException
{
    public function __construct($message = 'You have reached the limit allowed by your current plan.', $field = null)
    {
        parent::__construct($message, 'error', $field);
    }
}
