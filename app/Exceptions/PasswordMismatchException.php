<?php

namespace App\Exceptions;

class PasswordMismatchException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Password mismatch', 'error');
    }
}
