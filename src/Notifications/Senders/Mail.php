<?php

namespace Spatie\Backup\Notifications\Sender;

use Illuminate\Contracts\Mail\Mailer;
use Spatie\Backup\Notifications\BaseSender;

class Mail extends BaseSender
{
    /** @var \Illuminate\Contracts\Mail\Mailer */
    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send()
    {
        $this->mailer->raw($this->message, function (Message $message) {

            $emailConfig = config('laravel-backup.notifications.email');

            $message
                ->subject($this->subject)
                ->from($emailConfig['from'])
                ->to($emailConfig['to']);
        });
    }
}
