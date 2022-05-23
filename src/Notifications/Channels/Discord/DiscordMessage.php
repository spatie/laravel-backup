<?php

namespace Spatie\Backup\Notifications\Channels\Discord;

use Carbon\Carbon;

class DiscordMessage
{
    public const COLOR_SUCCESS = '0b6623';
    public const COLOR_WARNING = 'fD6a02';
    public const COLOR_ERROR = 'e32929';

    protected string $username = 'Laravel Backup';

    protected ?string $avatarUrl = null;

    protected string $title = '';

    protected string $description = '';

    protected array $fields = [];

    protected ?string $timestamp = null;

    protected ?string $footer = null;

    protected ?string $color = null;

    protected string $url = '';

    public function from(string $username, string $avatarUrl = null): self
    {
        $this->username = $username;

        if (! is_null($avatarUrl)) {
            $this->avatarUrl = $avatarUrl;
        }

        return $this;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function title($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function timestamp(Carbon $carbon): self
    {
        $this->timestamp = $carbon->toIso8601String();

        return $this;
    }

    public function footer(string $footer): self
    {
        $this->footer = $footer;

        return $this;
    }

    public function success(): self
    {
        $this->color = static::COLOR_SUCCESS;

        return $this;
    }

    public function warning(): self
    {
        $this->color = static::COLOR_WARNING;

        return $this;
    }

    public function error(): self
    {
        $this->color = static::COLOR_ERROR;

        return $this;
    }

    public function fields(array $fields, bool $inline = true): self
    {
        foreach ($fields as $label => $value) {
            $this->fields[] = [
                'name' => $label,
                'value' => $value,
                'inline' => $inline,
            ];
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username ?? 'Laravel Backup',
            'avatar_url' => $this->avatarUrl,
            'embeds' => [
                [
                    'title' => $this->title,
                    'url' => $this->url,
                    'type' => 'rich',
                    'description' => $this->description,
                    'fields' => $this->fields,
                    'color' => hexdec($this->color),
                    'footer' => [
                        'text' => $this->footer ?? '',
                    ],
                    'timestamp' => $this->timestamp ?? now(),
                ],
            ],
        ];
    }
}
