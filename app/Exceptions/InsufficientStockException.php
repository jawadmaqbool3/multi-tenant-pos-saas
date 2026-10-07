<?php

namespace App\Exceptions;

class InsufficientStockException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Cannot order more than available stock', 'error');
    }
}
