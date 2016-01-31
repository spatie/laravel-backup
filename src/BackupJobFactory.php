<?php

namespace Spatie\Backup;

class BackupJobFactory
{
    public static function createFromArray(array $config) : BackupJob
    {
        $backupJob = new BackupJob();

        //create backup destinations
        //create database dump

        return $backupJob;
    }
}