<?php

namespace App\Domain\Transfer\Exceptions;

use App\Domain\Shared\DomainExceptionBase;

class AuthorizationFailedException extends DomainExceptionBase
{
    public function __construct(string $message = 'Transfer not authorized')
    {
        parent::__construct($message);
    }
}
