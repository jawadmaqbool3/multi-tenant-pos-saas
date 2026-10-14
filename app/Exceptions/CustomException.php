<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    private $level;
    private $field;

    public function __construct($message = '', $level = 'error', $field = null)
    {
        parent::__construct($message);
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
