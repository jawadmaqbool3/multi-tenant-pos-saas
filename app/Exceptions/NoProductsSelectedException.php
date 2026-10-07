<?php

namespace App\Exceptions;

class NoProductsSelectedException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Select some products first', 'error');
    }
}
