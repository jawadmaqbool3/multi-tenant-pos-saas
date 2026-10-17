<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends Exception
{
    private $level;
    private $field;

    public function __construct($message = '', $level = 'error', $field = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->level = $level;
        $this->field = $field;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getField()
    {
        return $this->field;
    }
}
