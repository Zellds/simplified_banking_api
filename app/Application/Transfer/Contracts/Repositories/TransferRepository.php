<?php

namespace App\Application\Transfer\Contracts\Repositories;

use App\Domain\Transfer\TransferModel;

interface TransferRepository
{
    public function createPending(int $payerId, int $payeeId, float $amount): TransferModel;

    public function markAsApproved(TransferModel $transfer): void;

    public function markAsRejected(TransferModel $transfer): void;
}
