<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    private $level;
    public function __construct($message, $level)
    {
        $this->level = $level;
        $this->message = $message;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
