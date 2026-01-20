<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Wallet\Contracts\WalletRepository;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Persistence\Model\WalletModel;

class WalletRepositoryEloquent implements WalletRepository
{
    public function findByUserId(int $userId): ?Wallet
    {
        $model = WalletModel::query()
            ->where('user_id', $userId)
            ->first();

        return $model ? $model->toEntity() : null;
    }

    public function findByUserIdForUpdate(int $userId): ?Wallet
    {
        $model = WalletModel::query()
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        return $model ? $model->toEntity() : null;
    }

    public function hasSufficientBalance(Wallet $wallet, int $amount): bool
    {
        return $wallet->hasSufficientBalance($amount);
    }

    public function save(Wallet $wallet): void
    {
        WalletModel::query()
            ->whereKey($wallet->id)
            ->update(['balance' => $wallet->balance]);
    }
}
