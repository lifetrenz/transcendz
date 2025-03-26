<?php

namespace Lifetrenz\Transcendz\File;

use DateTime;
use ZipArchive;

class FileCompressor
{
    public function compressFile(
        string $zipFilename,
        string $filePath
    ): string {
        $zipFilePath = sprintf(
            "%s%s%s_%s.zip",
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            $zipFilename,
            (new DateTime())->format("His")
        );
        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE);
        $zip->addFile($filePath, basename($filePath));
        $zip->close();
        return $zipFilePath;
    }

    public function compressMultipleFiles(
        string $zipFilename,
        array $filePaths
    ): string {
        $zipFilePath = sprintf(
            "%s%s%s_%s.zip",
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            $zipFilename,
            (new DateTime())->format("His")
        );
        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE);
        foreach ($filePaths as $filePath) {
            $zip->addFile($filePath, basename($filePath));
        }
        $zip->close();
        return $zipFilePath;
    }
}
