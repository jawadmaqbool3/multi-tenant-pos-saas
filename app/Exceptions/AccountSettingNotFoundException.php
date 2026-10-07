<?php

namespace App\Exceptions;

class AccountSettingNotFoundException extends CustomException
{
    public function __construct($short)
    {
        parent::__construct("Account Setting (" . $short . ") not found.", 'error');
    }
}
