<?php

namespace App\Exceptions;

class EmailAlreadyTakenException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Email already has been taken', 'error');
    }
}
