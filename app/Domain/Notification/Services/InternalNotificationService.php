<?php

namespace App\Domain\Notification\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\InternalNewCustomerNotification;
use App\Notifications\InternalOrderPlacedNotification;
use App\Notifications\InternalOrderStatusUpdatedNotification;
use App\Notifications\InternalPaymentReceivedNotification;
use App\Notifications\InternalShipmentStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class InternalNotificationService
{
    public function sendNewCustomer(User $user): void
    {
        $this->notify(new InternalNewCustomerNotification($user));
    }

    public function sendOrderPlaced(Order $order): void
    {
        $this->notify(new InternalOrderPlacedNotification($order));
    }

    public function sendPaymentReceived(Payment $payment): void
    {
        $this->notify(new InternalPaymentReceivedNotification($payment));
    }

    public function sendOrderStatusUpdated(Order $order, string $fromStatus, string $toStatus, ?string $note = null): void
    {
        $this->notify(new InternalOrderStatusUpdatedNotification($order, $fromStatus, $toStatus, $note));
    }

    public function sendShipmentStatusUpdated(Shipment $shipment, string $fromStatus, string $toStatus): void
    {
        $this->notify(new InternalShipmentStatusUpdatedNotification($shipment, $fromStatus, $toStatus));
    }

    private function notify(object $notification): void
    {
        foreach ($this->recipients() as $email) {
            Notification::route('mail', $email)->notify($notification);
        }
    }

    /**
     * @return list<string>
     */
    private function recipients(): array
    {
        $recipients = config('notifications.internal.recipients', []);

        return is_array($recipients) ? array_values($recipients) : [];
    }
}
