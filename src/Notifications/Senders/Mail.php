<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Mail\Message;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Config\Repository;
use Spatie\Backup\Notifications\BaseSender;

class Mail extends BaseSender
{
    /** @var Mailer */
    protected $mailer;

    /** @var array */
    protected $config;

    /**
     * @param Mailer     $mailer
     * @param Repository $config
     */
    public function __construct(Mailer $mailer, Repository $config)
    {
        $this->config = $config->get('laravel-backup.notifications.mail');

        $this->mailer = $mailer;
    }

    public function send()
    {
        $this->mailer->raw($this->message, function (Message $message) {
            $message
                ->subject($this->subject)
                ->from($this->config['from'])
                ->to($this->config['to']);
        });
    }
}
