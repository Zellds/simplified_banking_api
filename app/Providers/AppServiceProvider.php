<?php

namespace App\Providers;

use App\Domain\Notification\Contracts\NotificationInterface;
use Illuminate\Support\ServiceProvider;
use App\Domain\Transfer\Contracts\AuthorizationInterface;
use App\Domain\User\UserRepository;
use App\Infrastructure\External\Notification\HttpNotificationClient;
use App\Domain\Wallet\WalletRepository;
use App\Infrastructure\External\Authorization\HttpAuthorizationClient;
use App\Domain\Transfer\TransferRepository;
use App\Infrastructure\Persistence\Repositories\UserRepositoryEloquent;
use App\Infrastructure\Persistence\Repositories\WalletRepositoryEloquent;
use App\Infrastructure\Persistence\Repositories\TransferRepositoryEloquent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(TransferRepository::class, TransferRepositoryEloquent::class);
        $this->app->bind(WalletRepository::class, WalletRepositoryEloquent::class);
        $this->app->bind(AuthorizationInterface::class, HttpAuthorizationClient::class);
        $this->app->bind(NotificationInterface::class, HttpNotificationClient::class);
    }
}
