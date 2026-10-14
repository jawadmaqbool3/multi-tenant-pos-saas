<?php

namespace App\Exceptions;

class PasswordMismatchException extends CustomException
{
    public function __construct($field = 'password_confirmation', $message = 'Password mismatch')
    {
        parent::__construct($message, 'error', $field);
    }
}
