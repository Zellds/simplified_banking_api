<?php

namespace App\Domain\Transfer\Exceptions;

use App\Domain\Shared\DomainExceptionBase;

class UnauthorizedTransferException extends DomainExceptionBase
{
    public function __construct(string $message = 'User is not allowed to transfer')
    {
        parent::__construct($message);
    }
}
