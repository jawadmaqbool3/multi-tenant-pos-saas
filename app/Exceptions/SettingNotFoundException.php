<?php

namespace App\Exceptions;

class SettingNotFoundException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Setting not found.', 'error');
    }
}
