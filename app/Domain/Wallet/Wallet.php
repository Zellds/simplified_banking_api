<?php

namespace App\Domain\Wallet;

class Wallet
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $balance,
    ) {}

    public function hasSufficientBalance(int $amountCents): bool
    {
        return $this->balance >= $amountCents;
    }

    public function debit(int $amountCents): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            balance: $this->balance - $amountCents
        );
    }

    public function credit(int $amountCents): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            balance: $this->balance + $amountCents
        );
    }
}
