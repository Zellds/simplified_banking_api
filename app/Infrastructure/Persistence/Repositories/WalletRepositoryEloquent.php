<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Wallet\Contracts\WalletRepository;
use App\Infrastructure\Persistence\Model\WalletModel;

class WalletRepositoryEloquent implements WalletRepository
{
    public function findByUserId(int $userId): ?WalletModel
    {
        return WalletModel::where('user_id', $userId)->first();
    }

    public function findByUserIdForUpdate(int $userId): ?WalletModel
    {
        return WalletModel::query()
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function hasSufficientBalance(WalletModel $wallet, float $amount): bool
    {
        return $wallet->balance >= $amount;
    }

    public function save(WalletModel $wallet): void
    {
        $wallet->save();
    }

    public function debit(WalletModel $wallet, float $amount): void
    {
        $wallet->balance -= $amount;
    }

    public function credit(WalletModel $wallet, float $amount): void
    {
        $wallet->balance += $amount;
    }
}
