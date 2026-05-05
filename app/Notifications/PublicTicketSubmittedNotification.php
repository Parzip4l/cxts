<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PublicTicketSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ticket ' . $this->ticket->ticket_number . ' berhasil dibuat')
            ->greeting('Halo ' . ($notifiable->name ?? 'Requester'))
            ->line('Ticket Anda sudah tercatat di CXTS.')
            ->line('Nomor ticket: ' . $this->ticket->ticket_number)
            ->line('Ringkasan: ' . $this->ticket->title)
            ->action('Track Ticket', route('public.tickets.track'))
            ->line('Gunakan nomor ticket dan email pelapor untuk melihat status terbaru.');
    }
}
