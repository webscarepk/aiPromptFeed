<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TempUserVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationUrl;

    public function __construct(string $verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        return $this->subject('Verify Your Account')
                    ->html(
                        "<p>Hello,</p>"
                        ."<p>Please click the link below to verify your email address and complete registration:</p>"
                        ."<p><a href='" . $this->verificationUrl . "'>Verify Email</a></p>"
                        ."<p>This link expires in 60 minutes.</p>"
                    );
    }
}
?>
