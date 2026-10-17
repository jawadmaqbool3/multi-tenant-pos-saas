<?php

namespace App\Exceptions;

use Throwable;

class ImageUploadFailedException extends CustomException
{
    public function __construct($message = 'Failed to upload image', $field = null, Throwable $previous = null)
    {
        parent::__construct($message, 'error', $field, $previous);
    }
}
