<?php

namespace App\Domain\Wallet\Contracts;

use App\Domain\Wallet\Wallet;

interface WalletRepository
{
    public function findByUserId(int $userId): ?Wallet;

    public function findByUserIdForUpdate(int $userId): ?Wallet;

    public function hasSufficientBalance(Wallet $wallet, int $amount): bool;

    public function save(Wallet $wallet): void;
}
