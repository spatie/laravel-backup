<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Contracts\Config\Repository;
use Telegram\Bot\Api;
use Spatie\Backup\Notifications\BaseSender;

class Telegram extends BaseSender
{
    /** @var \Telegram\Bot\Api */
    protected $telegram;

    /** @var array */
    protected $config;

    /** @var string */
    protected $chatId;

    /**
     * @param \Telegram\Bot\Api $telegram
     * @param Repository        $config
     */
    public function __construct(Api $telegram, Repository $config)
    {
        $this->config = $config->get('laravel-backup.notifications.telegram');

        $telegram->setAccessToken($this->config['bot_token']);
        $this->chatId = $this->config['chat_id'];

        $this->telegram = $telegram;
    }

    public function send()
    {
        $parameters = [
           'chat_id' => $this->chatId,
           'text' => $this->message,
           'disable_web_page_preview' => $this->config['disable_web_page_preview'],
        ];

        $this->telegram->sendMessage($parameters);
    }
}
