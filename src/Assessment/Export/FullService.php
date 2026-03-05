<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\Assessment\Data\ExportSettings;
use Edutiek\AssessmentService\Assessment\Data\ExportType;
use Edutiek\AssessmentService\Assessment\Data\ExportFile;

interface FullService
{
    public function getSettings(): ExportSettings;
    public function saveSettings(ExportSettings $settings);

    /**
     * Download the written texts as PDFs
     * @param WritingTask[] $writings
     * @return bool true, if a background task has started
     */
    public function downloadWritings(array $writings, bool $anonymous): bool;

    /**
     * Download the corrected texts as PDFs
     * @param WritingTask[] $writings
     * @return bool true, if a background task has started
     */
    public function downloadCorrections(array $writings, bool $anonymous_writer, bool $anonymous_corrector): bool;

    /**
     * Create an export file
     * @param ExportType $type
     ** @return bool true, if a background task has started
     */
    public function createFile(ExportType $type): bool;

    /**
     * @return ExportFile[]
     */
    public function getFiles(): array;

    /**
     * @param string[] $file_ids
     * @return ExportFile[]
     */
    public function getFilesByIds(array $file_ids): array;

    /**
     * @param string[] $file_ids
     * @return \SplFileInfo[]
     */
    public function getFilesInfos(array $file_ids): array;
    public function deleteFile(ExportFile $file): void;
    public function downloadFile(ExportFile $file): void;
}
