<?php 

namespace App\Domain\Wallet;

class Wallet
{
    public function __construct(
        public int $id,
        public int $userId,
        public float $balance,
    ) {}
}