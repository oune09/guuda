<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ActivationAutoriteMail extends Mailable
{
    public string $url;

    public function __construct(string $token)
    {
        $this->url = 'http://localhost:5173/pages/activation?token=' . $token;
    }

    public function build()
    {
        return $this
            ->subject('Activation de votre compte')
            ->view('emails.activation-autorite')
            ->with([
                'url' => $this->url,
            ]);
    }
}
