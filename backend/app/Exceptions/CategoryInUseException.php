<?php

namespace App\Exceptions;

use Exception;

class CategoryInUseException extends Exception
{
    public function __construct()
    {
        parent::__construct('A categoria possui lançamentos e não pode ser removida.');
    }
}
