<?php

namespace Spatie\Backup\Enums;

use ZipArchive;

enum Encryption: string
{
    case None = 'none';
    case Default = 'default';
    case Aes128 = 'aes128';
    case Aes192 = 'aes192';
    case Aes256 = 'aes256';

    public function algorithm(): ?int
    {
        return match ($this) {
            self::None => null,
            self::Default, self::Aes256 => defined(ZipArchive::class.'::EM_AES_256')
                ? ZipArchive::EM_AES_256
                : null,
            self::Aes128 => ZipArchive::EM_AES_128,
            self::Aes192 => ZipArchive::EM_AES_192,
        };
    }

    public function shouldEncrypt(): bool
    {
        return $this->algorithm() !== null;
    }
}
