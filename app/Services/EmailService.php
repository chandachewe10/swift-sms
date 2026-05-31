<?php

namespace App\Services;

use App\Mail\GenericEmail;
use App\Models\EmailConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(protected EmailConfig $config) {}

    /**
     * Build a one-off dynamic SMTP mailer from the stored config and send.
     *
     * Returns true on success, false on failure (error logged).
     */
    public function send(string $to, string $subject, string $body): bool
    {
        config()->set('mail.mailers.dynamic_smtp', [
            'transport'  => 'smtp',
            'host'       => $this->config->host,
            'port'       => $this->config->port,
            'encryption' => $this->config->encryption === 'none' ? null : $this->config->encryption,
            'username'   => $this->config->username,
            'password'   => $this->config->password,
        ]);

        try {
            Mail::mailer('dynamic_smtp')
                ->to($to)
                ->send(new GenericEmail(
                    $this->config->from_name,
                    $this->config->from_email,
                    $subject,
                    $body,
                ));

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailService send failed', ['to' => $to, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
