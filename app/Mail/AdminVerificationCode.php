<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($code, $user = null)
    {
        $this->code = $code;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Admin Verification Code')
                    ->view('emails.admin_verification')
                    ->with([
                        'code' => $this->code,
                        'user' => $this->user,
                    ]);
    }
}