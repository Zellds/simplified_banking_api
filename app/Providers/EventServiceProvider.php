<?php

namespace App\Providers;

use App\Domain\Transfer\Events\TransferCompleted;
use App\Infrastructure\Listeners\SendTransferNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferNotification::class,
        ],
    ];
}
