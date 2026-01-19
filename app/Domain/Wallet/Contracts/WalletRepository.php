<?php

namespace App\Domain\Wallet\Contracts;

use App\Domain\Wallet\WalletModel;

interface WalletRepository
{
    public function findByUserId(int $userId): ?WalletModel;

    public function findByUserIdForUpdate(int $userId): ?WalletModel;

    public function hasSufficientBalance(WalletModel $wallet, float $amount): bool;

    public function save(WalletModel $wallet): void;

    public function debit(WalletModel $wallet, float $amount): void;

    public function credit(WalletModel $wallet, float $amount): void;
}
