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
        return $this->subject('Verify Your Email - AiPromptFeed')
                    ->html(
                        "<html>
                        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <h2 style='color: #2c3e50;'>Welcome to AiPromptFeed!</h2>
                                
                                <p>Hi there,</p>
                                
                                <p>Thank you for signing up! Please verify your email address by clicking the button below to complete your registration:</p>
                                
                                <div style='text-align: center; margin: 30px 0;'>
                                    <a href='" . $this->verificationUrl . "' 
                                       style='display: inline-block; 
                                               background-color: #3498db; 
                                               color: white; 
                                               padding: 12px 30px; 
                                               text-decoration: none; 
                                               border-radius: 5px; 
                                               font-weight: bold;'>
                                        Verify Email Address
                                    </a>
                                </div>
                                
                                <p>Or copy and paste this link in your browser:</p>
                                <p style='word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 3px;'>
                                    <a href='" . $this->verificationUrl . "' style='color: #3498db;'>" . $this->verificationUrl . "</a>
                                </p>
                                
                                <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                                
                                <p style='font-size: 12px; color: #666;'>
                                    <strong>Note:</strong> This link expires in 60 minutes. If you didn't create this account, please ignore this email.
                                </p>
                                
                                <p style='color: #666;'>
                                    Best regards,<br>
                                    The AiPromptFeed Team
                                </p>
                            </div>
                        </body>
                        </html>"
                    );
    }
}
?>
