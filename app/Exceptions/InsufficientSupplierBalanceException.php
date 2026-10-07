<?php

namespace App\Exceptions;

class InsufficientSupplierBalanceException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Amount cannot be less than supplier balance', 'warning');
    }
}
