<?php

namespace App\Domain\Wallet\Exceptions;

use App\Domain\Shared\DomainExceptionBase;

class InsufficientBalanceException extends DomainExceptionBase
{
    public function __construct(string $message = 'Insufficient balance')
    {
        parent::__construct($message);
    }
}
