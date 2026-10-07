<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    private $level;

    public function __construct($message = '', $level = 'error')
    {
        parent::__construct($message);
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
