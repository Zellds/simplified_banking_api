<?php

namespace App\Providers;

use App\Application\Transfer\Contracts\Clients\AuthorizationClientInterface;
use App\Application\Transfer\Contracts\Clients\NotificationClientInterface;
use App\Application\Transfer\Contracts\Repositories\TransferRepository;
use App\Application\Transfer\Contracts\Repositories\UserRepository;
use App\Application\Transfer\Contracts\Repositories\WalletRepository;
use App\Infrastructure\External\Authorization\HttpAuthorizationClient;
use App\Infrastructure\External\Notification\HttpNotificationClient;
use App\Infrastructure\Persistence\Repositories\TransferRepositoryEloquent;
use App\Infrastructure\Persistence\Repositories\UserRepositoryEloquent;
use App\Infrastructure\Persistence\Repositories\WalletRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(TransferRepository::class, TransferRepositoryEloquent::class);
        $this->app->bind(WalletRepository::class, WalletRepositoryEloquent::class);
        $this->app->bind(AuthorizationClientInterface::class, HttpAuthorizationClient::class);
        $this->app->bind(NotificationClientInterface::class, HttpNotificationClient::class);
    }
}
