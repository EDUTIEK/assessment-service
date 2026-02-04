<?php

namespace Edutiek\AssessmentService\System\Spreadsheet;

interface FullService
{
    public function getNewSheet(?string $title, array $header, array $rows): Sheet;

    public function sheetsToFile(array $sheets, ExportType $type): string;

    /**
     * Get the data from a file as an array
     */
    public function dataFromFile(string $id, ?string $select_sheet = null): array;

    /**
     * Export data to a file
     * @param array<string, string> $header key/values of the header row
     * @param array<array<string, mixed>> $rows arrays of key/value of the content rows
     * @return string id of the stored file
     */
    public function dataToFile(array $header, array $rows, ExportType $type, string $title = null): string;
}
