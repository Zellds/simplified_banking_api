<?php

namespace App\Domain\Notification\Contracts;

interface NotificationInterface
{
    public function notify(string $message): bool;
}
