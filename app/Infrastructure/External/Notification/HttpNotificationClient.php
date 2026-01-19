<?php

namespace App\Infrastructure\External\Notification;

use App\Domain\Notification\Contracts\NotificationInterface;
use Illuminate\Support\Facades\Http;

class HttpNotificationClient implements NotificationInterface
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
