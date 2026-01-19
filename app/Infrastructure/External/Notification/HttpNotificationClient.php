<?php

namespace App\Infrastructure\External\Notification;

use App\Application\Transfer\Contracts\Clients\NotificationClientInterface;
use Illuminate\Support\Facades\Http;

class HttpNotificationClient implements NotificationClientInterface
{
    public function notify(string $message): bool
    {
        $response = Http::post(env('NOTIFICATION_SERVICE_URL'), [
            'message' => $message,
        ]);

        if ($response->successful()) {
            return true;
        }

        return false;
    }
}
