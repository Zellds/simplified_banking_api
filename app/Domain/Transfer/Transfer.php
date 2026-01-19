<?php

namespace App\Domain\Transfer;

class Transfer
{
    public function __construct(
        public int $id,
        public string $protocol,
        public int $payerId,
        public int $payeeId,
        public float $amount,
        public TransferStatus $status,
    ) {}
}