<?php

namespace App\Infrastructure\Listeners;

use App\Domain\Notification\Contracts\NotificationInterface;
use App\Domain\Transfer\Events\TransferCompleted;
use Illuminate\Support\Facades\Log;

class SendTransferNotification
{
    public function __construct(
        private NotificationInterface $notificationClient
    ) {}

    public function handle(TransferCompleted $event): void
    {
        try {
            $response = $this->notificationClient->notify(
                sprintf(
                    'Você recebeu uma transferência de %.2f. Protocolo: %s',
                    $event->amount,
                    $event->protocol
                )
            );

            if (!$response) {
                Log::channel('transfer')->info('Failed to send notification', ['transfer_protocol' => $event->protocol]);
                return;
            }
            
            Log::channel('transfer')->info('Notification sent', ['transfer_protocol' => $event->protocol]);
        } catch (\Throwable $e) {
            Log::channel('transfer')->info('Failed to send notification', ['transfer_protocol' => $event->protocol]);
        }
    }
}
