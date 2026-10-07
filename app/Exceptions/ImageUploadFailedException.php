<?php

namespace App\Exceptions;

class ImageUploadFailedException extends CustomException
{
    public function __construct()
    {
        parent::__construct('Failed to upload image', 'error');
    }
}
