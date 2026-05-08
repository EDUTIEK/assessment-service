<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\MarkedPdf;

use Edutiek\AssessmentService\EssayTask\Data\MarkedPdf;

interface UsageService
{
    /**
     * Get the marked pdf file id of a corrector
     */
    public function ownByIds(int $task_id, int $writer_id, int $corrector_id): ?string;

    /**
     * Get the marked pdf file id with correction marks of all authorized corrections
     */
    public function sumByIds(int $task_id, int $writer_id): ?string;

    /**
     * Save the file ids of marked pdf files
     * @param string $own_id    file_id of a pdf with own marks
     * @param string $all_id file_id of a pdf with own marks and marks all authorized previous correctors
     */
    public function save(string $own_id, string $all_id, int $task_id, int $writer_id, int $corrector_id): void;

    /**
     * Delete a marked pdf file
     */
    public function delete(int $task_id, int $writer_id, int $corrector_id): void;
}
