<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $admin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SISTEM: Pesanan ' . $this->order->order_code . ' Dibatalkan Otomatis',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_order_cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
