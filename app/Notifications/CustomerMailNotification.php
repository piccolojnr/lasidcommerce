<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

abstract class CustomerMailNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailData = $this->mailData($notifiable);

        return (new MailMessage)
            ->subject($mailData->subject)
            ->view('mail.customer.notification', [
                'mail' => $mailData,
                'appName' => config('app.name'),
            ])
            ->text('mail.customer.notification-text', [
                'mail' => $mailData,
                'appName' => config('app.name'),
            ]);
    }

    abstract protected function mailData(object $notifiable): CustomerMailData;

    protected function money(int $amount, string $currency): string
    {
        return sprintf('%s %s', strtoupper($currency), number_format($amount / 100, 2));
    }

    protected function statusLabel(string $status): string
    {
        return (string) Str::of($status)->replace('_', ' ')->title();
    }

    protected function formatDateTime(\DateTimeInterface $dateTime): string
    {
        return CarbonImmutable::instance($dateTime)
            ->utc()
            ->format('M j, Y g:i A').' UTC';
    }
}
