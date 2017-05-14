<?php

namespace Spatie\Backup\Tasks\Backup;

class Gzip
{
    public static function compress(string $inputFile): string
    {
        if (! file_exists($inputFile)) {
            throw new \InvalidArgumentException("Inputfile `{$inputFile}` does not exist.");
        }

        $inputHandle = fopen($inputFile, 'rb');

        $outputFile = $inputFile.'.gz';
        $outputHandle = gzopen($outputFile, 'w9');

        while (! feof($inputHandle)) {
            gzwrite($outputHandle, fread($inputHandle, 1024 * 512));
        }

        fclose($inputHandle);
        gzclose($outputHandle);

        return $outputFile;
    }
}
