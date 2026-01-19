<?php

namespace App\Application\Transfer\Events;

class TransferCompleted
{
    public function __construct(
        public readonly string $protocol,
        public readonly float $amount,
        public readonly int $payeeId,
    ) {}
}
