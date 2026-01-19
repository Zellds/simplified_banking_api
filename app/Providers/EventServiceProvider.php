<?php

namespace App\Providers;

use App\Application\Transfer\Events\TransferCompleted;
use App\Application\Transfer\Listeners\SendTransferNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransferCompleted::class => [
            SendTransferNotification::class,
        ],
    ];
}
