<?php

namespace App\Domain\Notification\Services;

use App\Domain\Auth\Services\StorefrontRedirectService;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\CustomerWelcomeNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\PaymentActionRequiredNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\ShipmentStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class CustomerNotificationService
{
    public function __construct(
        private StorefrontRedirectService $redirectService,
    ) {}

    public function sendWelcome(User $user): void
    {
        $user->notify(new CustomerWelcomeNotification(
            $this->redirectService->toSuccessUrl(),
        ));
    }

    public function sendOrderPlaced(Order $order): void
    {
        Notification::route('mail', $order->email)
            ->notify(new OrderPlacedNotification($order, $this->ordersUrl()));
    }

    public function sendPaymentActionRequired(Order $order, Payment $payment, string $authorizationUrl): void
    {
        Notification::route('mail', $order->email)
            ->notify(new PaymentActionRequiredNotification($order, $payment, $authorizationUrl));
    }

    public function sendPaymentReceived(Payment $payment): void
    {
        Notification::route('mail', $payment->order->email)
            ->notify(new PaymentReceivedNotification($payment, $this->ordersUrl()));
    }

    public function sendOrderStatusUpdated(Order $order, string $fromStatus, string $toStatus, ?string $note = null): void
    {
        Notification::route('mail', $order->email)
            ->notify(new OrderStatusUpdatedNotification($order, $fromStatus, $toStatus, $this->ordersUrl(), $note));
    }

    public function sendShipmentStatusUpdated(Shipment $shipment, string $fromStatus, string $toStatus): void
    {
        Notification::route('mail', $shipment->order->email)
            ->notify(new ShipmentStatusUpdatedNotification($shipment, $fromStatus, $toStatus, $this->ordersUrl()));
    }

    private function ordersUrl(): string
    {
        return $this->redirectService->toPathUrl(config('storefront.orders_path'));
    }
}
