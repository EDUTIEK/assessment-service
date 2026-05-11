<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\MarkedPdf;

use Edutiek\AssessmentService\EssayTask\Data\MarkedPdf;

/**
 * Servide to handle stored pdf files of essay with correction marks
 * This service is used when essay pdf files are directly marked instead of essay images
 *
 * When such a correction is authorized or pregraded, two pdf files ares sent from the corrector web app
 * - one with the essay having only the own correction marks of the corrector
 * - one with the essay having only the own correction marks and those of authorized corrections of previous correctors
 */
interface UsageService
{
    /**
     * Get the marked pdf file id with correction marks of a corrector
     */
    public function ownByIds(int $task_id, int $writer_id, int $corrector_id): ?string;

    /**
     * Get the marked pdf file id with combined correction marks of all authorized corrections for a task and writer
     */
    public function sumByIds(int $task_id, int $writer_id): ?string;

    /**
     * Save the file id of a pdf with own marks
     */
    public function saveOwn(string $file_id, int $task_id, int $writer_id, int $corrector_id): void;

    /**
     * Save the file id of a pdf with own marks and marks all authorized previous correctors
     */
    public function saveSum(string $file_id, int $task_id, int $writer_id, int $corrector_id): void;

    /**
     * Delete a marked pdf file
     */
    public function delete(int $task_id, int $writer_id, int $corrector_id): void;
}
