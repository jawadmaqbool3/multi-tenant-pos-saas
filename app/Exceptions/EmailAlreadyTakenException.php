<?php

namespace App\Exceptions;

class EmailAlreadyTakenException extends CustomException
{
    public function __construct($field = null, $message = 'Email already has been taken')
    {
        parent::__construct($message, 'error', $field);
    }
}
