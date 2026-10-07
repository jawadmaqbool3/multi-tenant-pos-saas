<?php

namespace App\Exceptions;

class AccountSettingNotSetException extends CustomException
{
    public function __construct($name)
    {
        parent::__construct("Account Setting (" . $name . ") not set.", 'error');
    }
}
