<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\View;

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
        $tpl = EmailTemplate::where(['key' => 'admin_verification', 'school_id' => null, 'is_active' => true])->first();
        if ($tpl) {
            $subject = $tpl->subject ?: 'Your Admin Verification Code';
            $html = View::make('emails.dynamic_template', [
                'html' => $tpl->body_html,
                'vars' => [
                    'user_name' => $this->user->name ?? 'Admin',
                    'verification_code' => $this->code,
                    'expire_minutes' => 5,
                ],
            ])->render();
            return $this->subject($subject)
                        ->view('emails.raw_html', ['content' => $html]);
        }

        // Fallback to static blade
        return $this->subject('Your Admin Verification Code')
                    ->view('emails.admin_verification')
                    ->with([
                        'code' => $this->code,
                        'user' => $this->user,
                    ]);
    }
}