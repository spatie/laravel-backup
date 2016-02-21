<?php

namespace Spatie\Backup\Notifications;

interface SendsNotifications
{
    /**
     * @param string $type
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setType($type);

    /**
     * @param string $subject
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setSubject($subject);

    /**
     * @param string $message
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setMessage($message);

    public function send();
}
