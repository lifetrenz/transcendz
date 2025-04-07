<?php

namespace Lifetrenz\Transcendz\File;

use DateTime;

class CsvGenerator
{
    private string $filePath;

    public function createCsvFile(string $filename)
    {
        $this->filePath = sprintf(
            "%s%s%s_%s.csv",
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            $filename,
            (new DateTime())->format("His")
        );

        return fopen($this->filePath, 'w');
    }

    public function appendLineToCsvFile(
        $fileHandle,
        array $reportFields
    ): int|false {
        return fputcsv($fileHandle, $reportFields);
    }

    public function appendMultipleLinesToCsvFile(
        $fileHandle,
        array $dataset
    ): array {
        return array_map(
            fn ($dataRow) => fputcsv($fileHandle, (array)$dataRow),
            $dataset
        );
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function closeCsvFile(
        $fileHandle
    ): bool {
        return fclose($fileHandle);
    }
}
