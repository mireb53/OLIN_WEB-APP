<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use App\Models\EmailTemplate;
use Illuminate\Notifications\Notification;

class VerifyEmailWithCode extends Notification
{
    use Queueable;

    public $verificationCode;

    /**
     * Create a new notification instance.
     */
    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Try DB template first
        $tpl = EmailTemplate::where(['key' => 'verify_email', 'school_id' => null, 'is_active' => true])->first();
        if ($tpl) {
            $subject = $tpl->subject;
            // Render HTML using a generic view wrapper that accepts raw HTML
            $html = View::make('emails.dynamic_template', [
                'html' => $tpl->body_html,
                'vars' => [
                    'user_name' => $notifiable->name ?? 'User',
                    'verification_code' => $this->verificationCode,
                    'expire_minutes' => config('auth.verification.expire', 60),
                ],
            ])->render();

            return (new MailMessage)
                ->subject($subject)
                ->view('emails.raw_html', ['content' => $html]);
        }

        // Default fallback
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Thanks for signing up! Please use the following code to verify your email address:')
            ->line('Verification Code: **' . $this->verificationCode . '**')
            ->line('This code will expire in ' . config('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}