<?php

namespace Tests\Unit\Application\Transfer;

use App\Application\Transfer\TransferService;
use App\Application\Transfer\Contracts\Clients\AuthorizationClientInterface;
use App\Application\Transfer\Contracts\Repositories\TransferRepository;
use App\Application\Transfer\Contracts\Repositories\UserRepository;
use App\Application\Transfer\Contracts\Repositories\WalletRepository;
use App\Domain\Transfer\Exceptions\UnauthorizedTransferException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Transfer\TransferModel;
use App\Domain\User\UserModel;
use App\Domain\Wallet\WalletModel;
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
    private AuthorizationClientInterface $authorizationClient;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        DB::shouldReceive('transaction')
            ->byDefault()
            ->andReturnUsing(fn ($callback) => $callback());

        DB::shouldReceive('afterCommit')
            ->byDefault()
            ->andReturnUsing(fn ($callback) => $callback());

        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->walletRepository = Mockery::mock(WalletRepository::class);
        $this->transferRepository = Mockery::mock(TransferRepository::class);
        $this->authorizationClient = Mockery::mock(AuthorizationClientInterface::class);

        $this->service = new TransferService(
            $this->userRepository,
            $this->walletRepository,
            $this->transferRepository,
            $this->authorizationClient
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
        $merchant = new UserModel(['type' => 'merchant']);
        $merchant->id = 1;

        $payee = new UserModel(['type' => 'common']);
        $payee->id = 2;

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($merchant);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_same_user_cannot_transfer_to_themselves(): void
    {
        $user = new UserModel(['type' => 'common']);
        $user->id = 1;

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($user);

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 1, 100);
    }

    public function test_throws_exception_when_wallet_not_found(): void
    {
        $payer = new UserModel(['type' => 'common']);
        $payer->id = 1;

        $payee = new UserModel(['type' => 'common']);
        $payee->id = 2;

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn(null);

        $this->expectException(WalletNotFoundException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_throws_exception_when_balance_is_insufficient(): void
    {
        $payer = new UserModel(['type' => 'common']);
        $payer->id = 1;

        $payee = new UserModel(['type' => 'common']);
        $payee->id = 2;

        $wallet = new WalletModel(['balance' => 50]);
        $wallet->user_id = 1;

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($wallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with($wallet, 100)
            ->andReturn(false);

        $this->expectException(InsufficientBalanceException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_transfer_is_rejected_when_authorizer_denies(): void
    {
        $payer = new UserModel(['type' => 'common']);
        $payer->id = 1;

        $payee = new UserModel(['type' => 'common']);
        $payee->id = 2;

        $wallet = new WalletModel(['balance' => 1000]);
        $wallet->user_id = 1;

        $transfer = new TransferModel(['protocol' => 'TX123']);

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($wallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with($wallet, 100)
            ->andReturn(true);

        $this->transferRepository
            ->shouldReceive('createPending')
            ->with(1, 2, 100)
            ->andReturn($transfer);

        $this->authorizationClient->shouldReceive('authorize')->andReturn(false);

        $this->transferRepository
            ->shouldReceive('markAsRejected')
            ->with($transfer)
            ->once();

        $this->expectException(UnauthorizedTransferException::class);

        $this->service->execute(1, 2, 100);
    }

    public function test_completes_transfer_and_returns_protocol(): void
    {
        $payer = new UserModel(['type' => 'common']);
        $payer->id = 1;

        $payee = new UserModel(['type' => 'common']);
        $payee->id = 2;

        $wallet = new WalletModel(['balance' => 1000]);
        $wallet->user_id = 1;

        $payerWallet = new WalletModel(['balance' => 1000]);
        $payerWallet->user_id = 1;

        $payeeWallet = new WalletModel(['balance' => 0]);
        $payeeWallet->user_id = 2;

        $transfer = new TransferModel(['protocol' => 'TX123']);

        $this->userRepository->shouldReceive('findById')->with(1)->andReturn($payer);
        $this->userRepository->shouldReceive('findById')->with(2)->andReturn($payee);

        $this->walletRepository->shouldReceive('findByUserId')->with(1)->andReturn($wallet);

        $this->walletRepository
            ->shouldReceive('hasSufficientBalance')
            ->with($wallet, 100)
            ->andReturn(true);

        $this->transferRepository
            ->shouldReceive('createPending')
            ->with(1, 2, 100)
            ->andReturn($transfer);

        $this->authorizationClient->shouldReceive('authorize')->andReturn(true);

        $this->walletRepository->shouldReceive('findByUserIdForUpdate')->with(1)->andReturn($payerWallet);
        $this->walletRepository->shouldReceive('findByUserIdForUpdate')->with(2)->andReturn($payeeWallet);

        $this->walletRepository->shouldReceive('debit')->with($payerWallet, 100)->once();
        $this->walletRepository->shouldReceive('credit')->with($payeeWallet, 100)->once();

        $this->walletRepository->shouldReceive('save')->with($payerWallet)->once();
        $this->walletRepository->shouldReceive('save')->with($payeeWallet)->once();

        $this->transferRepository->shouldReceive('markAsApproved')->with($transfer)->once();

        $protocol = $this->service->execute(1, 2, 100);

        $this->assertSame('TX123', $protocol);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
