<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleNotificatiosMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $saleDetails;

    /**
     * Create a new message instance.
     * 
     * @param $sale
     * @param $saleDetails
     */
    public function __construct($sale, $saleDetails)
    {
        $this->sale = $sale;
        $this->saleDetails = $saleDetails;
    }

    /**
     * Get the message envelope.
     * 
     * @return $this
     */

     
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'NEW SALE',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sale_notification',
             with: [
                'sale' => $this->sale,
                'saleDetails' => $this->saleDetails
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
