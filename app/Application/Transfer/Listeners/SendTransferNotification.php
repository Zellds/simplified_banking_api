<?php

namespace App\Application\Transfer\Listeners;

use App\Application\Transfer\Events\TransferCompleted;
use App\Application\Transfer\Contracts\Clients\NotificationClientInterface;
use Illuminate\Support\Facades\Log;

class SendTransferNotification
{
    public function __construct(
        private NotificationClientInterface $notificationClient
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
