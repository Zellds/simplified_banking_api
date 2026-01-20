<?php

namespace Tests\Unit\Application\Transfer;

use App\Application\Services\TransferService;
use App\Domain\Transfer\Contracts\AuthorizationInterface;
use App\Domain\Transfer\Contracts\TransferRepository;
use App\Domain\User\Contracts\UserRepository;
use App\Domain\Wallet\Contracts\WalletRepository;
use App\Domain\Transfer\Exceptions\UnauthorizedTransferException;
use App\Domain\Transfer\Transfer;
use App\Domain\Transfer\TransferStatus;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\User\User;
use App\Domain\Wallet\Wallet;
use App\Infrastructure\Persistence\Model\UserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    private TransferService $service;
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private TransferRepository $transferRepository;
    private AuthorizationInterface $authorization;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        DB::shouldReceive('transaction')
            ->byDefault()
            ->andReturnUsing(fn($callback) => $callback());

        DB::shouldReceive('afterCommit')
            ->byDefault()
            ->andReturnUsing(fn($callback) => $callback());

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->walletRepository = Mockery::mock(WalletRepository::class);
        $this->transferRepository = Mockery::mock(TransferRepository::class);
        $this->authorization = Mockery::mock(AuthorizationInterface::class);

        $this->service = new TransferService(
            $this->userRepository,
            $this->walletRepository,
            $this->transferRepository,
            $this->authorization
        );
    }

    private function makeUserEntity(int $id, string $type = 'common'): User
    {
        $name = $type === 'merchant' ? 'Merchant User' : 'Common User';
        $email = strtolower(str_replace(' ', '.', $name)) . "{$id}@example.com";
        $document = str_pad((string) $id, 11, '0', STR_PAD_LEFT);

        $model = new UserModel([
            'name' => $name,
            'email' => $email,
            'document' => $document,
            'type' => $type,
        ]);

        $model->id = $id;

        return $model->toEntity();
    }

    private function makeWalletEntity(int $id, int $userId, int $balance): Wallet
    {
        return new Wallet(
            id: $id,
            userId: $userId,
            balance: $balance
        );
    }

    public function test_throws_exception_when_payer_or_payee_not_found(): void
    {
        $this->userRepository->shouldReceive('findById')->andReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_merchant_cannot_make_transfer(): void
    {
        $merchant = $this->makeUserEntity(id: 1, type: 'merchant');
        $payee = $this->makeUserEntity(id: 2, type: 'common');

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($merchant);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_same_user_cannot_transfer_to_themselves(): void
    {
        $payer = $this->makeUserEntity(id: 1, type: 'common');

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 1, 100);
    }

    public function test_throws_exception_when_wallet_not_found(): void
    {
        $payer = $this->makeUserEntity(id: 1, type: 'common');
        $payee = $this->makeUserEntity(id: 2, type: 'common');

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn(null);
        $this->walletRepository->shouldReceive('findByUserId')->with(2)->andReturn(null);

        $this->expectException(WalletNotFoundException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_throws_exception_when_balance_is_insufficient(): void
    {
        $payer = $this->makeUserEntity(id: 1, type: 'common');
        $payee = $this->makeUserEntity(id: 2, type: 'common');

        $payerWallet = $this->makeWalletEntity(id: 10, userId: 1, balance: 50);
        $payeeWallet = $this->makeWalletEntity(id: 11, userId: 2, balance: 500);

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($payerWallet);
        $this->walletRepository->shouldReceive('findByUserId')->with(2)->andReturn($payeeWallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with($payerWallet, 100)
            ->andReturn(false);

        $this->expectException(InsufficientBalanceException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_transfer_is_rejected_when_authorizer_denies(): void
    {
        $payer = $this->makeUserEntity(id: 1, type: 'common');
        $payee = $this->makeUserEntity(id: 2, type: 'common');

        $payerWallet = $this->makeWalletEntity(id: 10, userId: 1, balance: 100000);
        $payeeWallet = $this->makeWalletEntity(id: 11, userId: 2, balance: 100000);

        $transfer = new Transfer(
            id: 1,
            protocol: 'TX123',
            payerId: 1,
            payeeId: 2,
            amount: 100,
            status: TransferStatus::PENDING
        );

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($payerWallet);
        $this->walletRepository->shouldReceive('findByUserId')->with(2)->andReturn($payeeWallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with($payerWallet, 100)
            ->andReturn(true);

        $this->transferRepository
            ->shouldReceive('createPending')
            ->with(1, 2, 100)
            ->andReturn($transfer);

        $this->authorization->shouldReceive('authorize')->andReturn(false);

        $this->transferRepository
            ->shouldReceive('markAsRejected')
            ->with($transfer)
            ->once();

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_completes_transfer_and_returns_protocol(): void
    {
        $payer = $this->makeUserEntity(id: 1, type: 'common');
        $payee = $this->makeUserEntity(id: 2, type: 'common');

        $payerWallet = $this->makeWalletEntity(id: 10, userId: 1, balance: 100000);
        $payeeWallet = $this->makeWalletEntity(id: 20, userId: 2, balance: 0);

        $payerWalletLocked = $this->makeWalletEntity(id: 10, userId: 1, balance: 100000);
        $payeeWalletLocked = $this->makeWalletEntity(id: 20, userId: 2, balance: 0);

        $transfer = new Transfer(id: 1, protocol: 'TX123', payerId: 1, payeeId: 2, amount: 100, status: TransferStatus::PENDING);

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($payerWallet);
        $this->walletRepository->shouldReceive('findByUserId')->with(2)->andReturn($payeeWallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with(Mockery::on(fn($w) => $w instanceof \App\Domain\Wallet\Wallet && $w->userId === 1), 100)
            ->andReturn(true);

        $this->transferRepository
            ->shouldReceive('createPending')
            ->with(1, 2, 100)
            ->andReturn($transfer);

        $this->authorization->shouldReceive('authorize')->andReturn(true);

        $this->walletRepository
            ->shouldReceive('findByUserIdForUpdate')
            ->with(1)
            ->andReturn($payerWalletLocked);

        $this->walletRepository
            ->shouldReceive('findByUserIdForUpdate')
            ->with(2)
            ->andReturn($payeeWalletLocked);

        $this->walletRepository
            ->shouldReceive('save')
            ->with(Mockery::on(fn(Wallet $w) => $w->userId === 1 && $w->balance === 99900))
            ->once();

        $this->walletRepository
            ->shouldReceive('save')
            ->with(Mockery::on(fn(Wallet $w) => $w->userId === 2 && $w->balance === 100))
            ->once();

        $this->transferRepository
            ->shouldReceive('markAsApproved')
            ->with($transfer)
            ->once();

        $protocol = $this->service->execute(1, 2, 100);

        $this->assertSame('TX123', $protocol);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
