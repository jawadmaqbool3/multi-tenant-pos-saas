<?php

namespace App\Exceptions;

class InvalidFieldException extends CustomException
{
    public function __construct($field, $message = null)
    {
        parent::__construct(
            $message ?: ucfirst(str_replace('_', ' ', $field)) . ' is invalid.',
            'error',
            $field
        );
    }
}
