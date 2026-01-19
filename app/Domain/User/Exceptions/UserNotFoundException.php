<?php

namespace App\Domain\User\Exceptions;

use App\Domain\Shared\DomainExceptionBase;

class UserNotFoundException extends DomainExceptionBase
{
    public function __construct(string $message = 'User not found')
    {
        parent::__construct($message);
    }
}
