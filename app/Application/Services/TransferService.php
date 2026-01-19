<?php

namespace App\Application\Services;

use App\Domain\Transfer\Contracts\TransferRepository;
use App\Domain\User\Contracts\UserRepository;
use App\Domain\Wallet\Contracts\WalletRepository;
use App\Domain\Transfer\Contracts\AuthorizationInterface;
use App\Domain\Transfer\Events\TransferCompleted;
use App\Domain\Transfer\Exceptions\UnauthorizedTransferException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        private UserRepository $userRepository,
        private WalletRepository $walletRepository,
        private TransferRepository $transferRepository,
        private AuthorizationInterface $authorization,
    ) {}

    public function execute(int $payerId, int $payeeId, float $amount): string
    {
        $payer = $this->userRepository->findById($payerId);
        $payee = $this->userRepository->findById($payeeId);

        if (!$payer || !$payee) {
            throw new UserNotFoundException();
        }

        if ($payerId === $payeeId) {
            throw new UnauthorizedTransferException('Payer and payee cannot be the same user.');
        }

        if ($payer->isMerchant()) {
            throw new UnauthorizedTransferException('Merchants are not allowed to initiate transfers.');
        }

        $wallet = $this->walletRepository->findByUserId($payer->id);

        if (!$wallet) {
            throw new WalletNotFoundException();
        }

        if (!$this->walletRepository->hasSufficientBalance($wallet, $amount)) {
            throw new InsufficientBalanceException();
        }

        $transfer = $this->transferRepository->createPending($payer->id, $payee->id, $amount);

        if (!$this->authorization->authorize()) {
            $this->transferRepository->markAsRejected($transfer);
            throw new UnauthorizedTransferException('Transfer not authorized.');
        }

        DB::transaction(function () use ($payer, $payee, $amount, $transfer) {
            $payerWallet = $this->walletRepository->findByUserIdForUpdate($payer->id);
            $payeeWallet = $this->walletRepository->findByUserIdForUpdate($payee->id);

            $this->walletRepository->debit($payerWallet, $amount);
            $this->walletRepository->credit($payeeWallet, $amount);

            $this->walletRepository->save($payerWallet);
            $this->walletRepository->save($payeeWallet);

            $this->transferRepository->markAsApproved($transfer);

            DB::afterCommit(function () use ($transfer, $amount, $payee) {
                event(new TransferCompleted($transfer->protocol, $amount, $payee->id));
            });
        });

        return $transfer->protocol;
    }
}
