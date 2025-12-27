<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

interface FullService
{
    /**
     * Process an uploaded ZIP file
     * - Check the relevant files in the zip and store them temporarily
     */
    public function processZipFile(string $temp_file_id, ?string $password, ?string $required_hash): ImportResult;

    /**
     * Get the files that are relevant for the import
     * Some of these files may have errors and are not importable
     * @return ImportFile[]
     */
    public function relevantFiles(): array;

    /**
     * Get the table columns to present the relevant files
     * @return array<string, Column>
     */
    public function tableColumns(): array;

    /**
     * Get the table rows to present the relevant files
     * @return Row[]
     */
    public function tableRows(): array;

    /**
     * Import the files
     * @param $overwrite_existing
     * @return int  number of imported files
     */
    public function importFiles($overwrite_existing = false): int;

    /**
     * Delete all temporary files and session values
     */
    public function cleanup(): void;
}
