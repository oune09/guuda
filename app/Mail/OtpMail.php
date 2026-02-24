<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $otp;

    public function __construct(int $otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Votre code de vérification')
                    ->view('emails.otp');
    }
}
