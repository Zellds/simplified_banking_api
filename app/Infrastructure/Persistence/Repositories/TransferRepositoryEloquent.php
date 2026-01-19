<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Transfer\Contracts\TransferRepository;
use App\Domain\Transfer\Transfer;
use App\Infrastructure\Persistence\Model\TransferModel;
use App\Domain\Transfer\TransferStatus;
use Illuminate\Support\Str;

class TransferRepositoryEloquent implements TransferRepository
{
    public function createPending(int $payerId, int $payeeId, float $amount): Transfer
    {
        $transfer = new TransferModel();

        $transfer->protocol = Str::uuid();
        $transfer->payer_id = $payerId;
        $transfer->payee_id = $payeeId;
        $transfer->amount = $this->normalizeMoney($amount);
        $transfer->status = TransferStatus::PENDING->value;

        $transfer->save();

        return $transfer->toEntity();
    }

    public function markAsApproved(Transfer $transfer): void
    {
        TransferModel::query()->where('id', $transfer->id)->update([
            'status' => TransferStatus::APPROVED->value,
        ]);
    }

    public function markAsRejected(Transfer $transfer): void
    {
        TransferModel::query()->where('id', $transfer->id)->update([
            'status' => TransferStatus::REJECTED->value,
        ]);
    }

    private function normalizeMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
