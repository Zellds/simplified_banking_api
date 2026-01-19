<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Transfer\Contracts\Repositories\TransferRepository;
use App\Domain\Transfer\TransferModel;
use App\Domain\Transfer\TransferStatus;
use Illuminate\Support\Str;

class TransferRepositoryEloquent implements TransferRepository
{
    public function createPending(int $payerId, int $payeeId, float $amount): TransferModel
    {
        $transfer = new TransferModel();

        $transfer->protocol = Str::uuid();
        $transfer->payer_id = $payerId;
        $transfer->payee_id = $payeeId;
        $transfer->amount = $this->normalizeMoney($amount);
        $transfer->status = TransferStatus::PENDING->value;

        $transfer->save();

        return $transfer;
    }

    public function markAsApproved(TransferModel $transfer): void
    {
        $transfer->status = TransferStatus::APPROVED->value;
        $transfer->save();
    }

    public function markAsRejected(TransferModel $transfer): void
    {
        $transfer->status = TransferStatus::REJECTED->value;
        $transfer->save();
    }

    private function normalizeMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
