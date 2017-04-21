<?php


namespace Spatie\Backup\Tasks\Backup;

class Gzip
{

    /** @var string */
    public $originalFilePath;

    /** @var string */
    public $filePath;

    /** @var bool */
    public $failed;

    public function __construct(string $filePath)
    {
        $this->originalFilePath = $filePath;

        $this->filePath = $this->compress();

        $this->failed = $this->originalFilePath == $this->filePath;
    }

    /**
     * @return string
     */
    protected function compress()
    {
        $gzipPath = $this->originalFilePath.'.gz';

        if ($gzipOut = gzopen($gzipPath, 'w9')) {
            if ($gzipIn = fopen($this->originalFilePath, 'rb')) {
                while (! feof($gzipIn)) {
                    gzwrite($gzipOut, fread($gzipIn, 1024 * 512));
                }
                fclose($gzipIn);
            } else {
                return $this->originalFilePath;
            }

            gzclose($gzipOut);
        } else {
            return $this->originalFilePath;
        }

        return $gzipPath;
    }

    /**
     * @return int
     */
    public function oldFileSize()
    {
        return filesize($this->originalFilePath);
    }

    /**
     * @return int
     */
    public function newFileSize()
    {
        return filesize($this->filePath);
    }
}
