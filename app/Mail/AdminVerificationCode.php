<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

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
            // Resolve variables
            $appName = config('app.name');
            try {
                if (\Schema::hasTable('settings')) {
                    $global = Setting::whereNull('school_id')->first();
                    if ($global && !empty($global->platform_name)) {
                        $appName = $global->platform_name;
                    }
                }
            } catch (\Throwable $e) {}
            $verifyUrl = URL::route('verification.notice');
            $vars = [
                'app_name' => $appName,
                'user_name' => $this->user->name ?? 'Admin',
                'verification_code' => $this->code,
                'expire_minutes' => 5,
                'verify_url' => $verifyUrl,
            ];

            // Process subject placeholders
            $subject = $tpl->subject ?: 'Your Admin Verification Code';
            foreach ($vars as $k => $v) {
                $subject = str_replace('{{'.$k.'}}', (string) $v, $subject);
                $subject = str_replace('{{ '.$k.' }}', (string) $v, $subject);
            }
            $html = View::make('emails.dynamic_template', [
                'html' => $tpl->body_html,
                'vars' => $vars,
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