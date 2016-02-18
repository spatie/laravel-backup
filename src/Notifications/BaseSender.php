<?php

namespace Spatie\Backup\Notifications;

abstract class BaseSender implements SendsNotifications
{
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';

    /** @var type */
    protected $type;

    /** @var string */
    protected $subject;

    /** @var string */
    protected $message;

    public function setType(string $type) : SendsNotifications
    {
        $this->type = $type;

        return $this;
    }

    public function setSubject(string $subject) : SendsNotifications
    {
        $this->subject = $subject;

        return $this;
    }

    public function setMessage(string $message) : SendsNotifications
    {
        $this->message = $message;

        return $this;
    }
}
