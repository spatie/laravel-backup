<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
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

            $emailConfig = config('laravel-backup.notifications.mail');

            $message
                ->subject($this->subject)
                ->from($emailConfig['from'])
                ->to($emailConfig['to']);
        });
    }
}
