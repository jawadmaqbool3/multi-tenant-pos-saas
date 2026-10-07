<?php

namespace App\Exceptions;

class RoleDoesNotExistException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Role does not exist', 'error');
    }
}
