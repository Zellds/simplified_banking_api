<?php

namespace App\Domain\Transfer\Contracts;

use App\Domain\Transfer\Transfer;
use App\Infrastructure\Persistence\Model\TransferModel;

interface TransferRepository
{
    public function createPending(int $payerId, int $payeeId, float $amount): Transfer;

    public function markAsApproved(Transfer $transfer): void;

    public function markAsRejected(Transfer $transfer): void;
}
