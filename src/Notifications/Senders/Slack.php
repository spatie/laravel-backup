<?php

namespace Spatie\Backup\Notifications\Sender;

use Spatie\Backup\Notifications\BaseSender;

class Slack extends BaseSender
{
    /** @var \Maknz\Slack\Client */
    protected $client;

    public function __construct(\Maknz\Slack\Client $client)
    {
        $this->client = $client;
    }

    public function send()
    {
        $slackConfig = config('laravel-backup.notifications.slack');

        $this->client->to($slackConfig['channel'])->attach([
            'text' => $this->message,
            'color' => $this->type === self::TYPE_ERROR ? 'warning' : '',
        ])->send($this->subject);
    }
}
