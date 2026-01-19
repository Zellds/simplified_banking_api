<?php

namespace App\Application\Transfer\Contracts\Clients;

interface NotificationClientInterface
{
    public function notify(string $message): bool;
}
