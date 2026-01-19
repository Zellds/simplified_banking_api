<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Transfer\Contracts\Repositories\WalletRepository;
use App\Domain\Wallet\WalletModel;

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
