<?php

namespace App\Exceptions;

class NoProductsAddedException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Add some products first', 'warning');
    }
}
