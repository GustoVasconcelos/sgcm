<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScaleShipped extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfContent;
    public $period;
    public $senderName;

    // Recebemos o conteúdo, o período e o nome de quem enviou
    public function __construct($pdfContent, $period, $senderName)
    {
        $this->pdfContent = $pdfContent;
        $this->period = $period;
        $this->senderName = $senderName;
    }

    public function build()
    {
        $filename = 'Escala_' . str_replace([' ', '/'], ['_', '-'], $this->period) . '.pdf';

        return $this->subject('Escala ' . $this->period)
                    ->view('emails.scale_plain')
                    ->attachData($this->pdfContent, $filename, [
                        'mime' => 'application/pdf',
                    ]);
    }
}