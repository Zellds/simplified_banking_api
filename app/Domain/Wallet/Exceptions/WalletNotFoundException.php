<?php

namespace App\Domain\Wallet\Exceptions;

use App\Domain\Shared\DomainExceptionBase;

class WalletNotFoundException extends DomainExceptionBase
{
    public function __construct(string $message = 'Wallet not found')
    {
        parent::__construct($message);
    }
}
