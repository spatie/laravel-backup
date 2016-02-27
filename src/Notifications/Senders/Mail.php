<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Mail\Message;
use Spatie\Backup\Notifications\BaseSender;

class Mail extends BaseSender
{
    /** @var \Illuminate\Contracts\Mail\Mailer */
    protected $mailer;

    /** @var array */
    protected $config;

    /**
     * @param \Illuminate\Contracts\Mail\Mailer       $mailer
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct($mailer, $config)
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
