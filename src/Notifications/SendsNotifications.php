<?php

namespace Spatie\Backup\Notifications;

interface SendsNotifications
{
    public function setType(string $type) : SendsNotifications;

    public function setSubject(string $subject) : SendsNotifications;

    public function setMessage(string $message) : SendsNotifications;

    public function send();
}
