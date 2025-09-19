<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\Corrector;

interface FullService
{
    /**
     * @param CorrectorSummary $summary
     * @param int              $user_id User executing this operation
     * @return void
     */
    public function authorizeCorrection(CorrectorSummary $summary, int $user_id): void;

    /**
     * @param Writer $writer
     * @param int    $user_id User executing this operation
     * @return bool
     */
    public function removeAuthorizations(int $task_id, Writer $writer, int $user_id): bool;

    /**
     * @param Writer $writer
     * @param int    $user_id User executing this operation
     * @return bool
     */
    public function removeCorrectorAuthorizations(Corrector $corrector, int $user_id): bool;

    const BLANK_CORRECTOR_ASSIGNMENT = -1;
    const UNCHANGED_CORRECTOR_ASSIGNMENT = -2;

    /**
     * Reassigns a couple of correctors to multiple writer
     * - first and second corrector cannot be the same -> invalid
     * - already authorized corrections are not changed -> unchanged
     * - if both assignments are untouched -> unchanged
     * - if one assignment changes -> changed
     * - existing correction summaries and comments are moved to the new corrector -> changed
     * - if the assignment of an existing correction is removed the summaries and comments are removed too!
     * - criterion points are removed if an existing correction is changed or removed because they can be individual
     *   and not reused by the new assigned corrector
     *
     * @param int $task_id
     * @param int $first_corrector
     * @param int $second_corrector
     * @param int[] $writer_ids
     * @param bool $dry_run
     * @return array[] ["changed" => int[], "unchanged" => int[], "invalid" => int[]] associative list of writer ids
     */
    public function assignMultiple(
        int $task_id,
        int $first_corrector,
        int $second_corrector,
        array $writer_ids,
        $dry_run = false
    ): array;

    /**
     * Assign correctors to empty corrector positions for the candidates
     * @return int number of new assignments
     */
    public function assignMissing(int $task_id): int;
}
