<?php

namespace Spatie\Backup\Notifications\Senders;

use Illuminate\Contracts\Config\Repository;
use Telegram\Bot\Api;
use Spatie\Backup\Notifications\BaseSender;

class Telegram extends BaseSender
{
    /** @var \Telegram\Bot\Api */
    protected $api;

    /** @var array */
    protected $config;

    /** @var string */
    protected $chat_id;

    /**
     * @param \Telegram\Bot\Api $api
     * @param Repository        $config
     */
    public function __construct(Api $api, Repository $config)
    {
        $this->config = $config->get('laravel-backup.notifications.telegram');

        $api->setAccessToken($this->config['bot_token']);
        $this->chat_id = $this->config['chat_id'];

        $this->api = $api;
    }

    public function send()
    {
        $params = [
           'chat_id' => $this->chat_id,
           'text' => $this->message,
           'disable_web_page_preview' => true,
        ];

        $this->api->sendMessage($params);
    }
}
