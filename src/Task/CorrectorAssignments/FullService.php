<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;

interface FullService extends ReadService
{
    public const BLANK_CORRECTOR_ASSIGNMENT = -1;
    public const UNCHANGED_CORRECTOR_ASSIGNMENT = -2;

    /**
     * Get the current correction filter set by a corrector
     * @return array [?array $grading_status, ?int $position]
     */
    public function getCorrectionFilter(int $corrector_id): array;

    /**
     * Save a filter for showing assignments to a corrector
     * This is set on the start page of a corrector
     * This is used to filter the assigned items in the corrector app
     *
     * @param GradingStatus[]|null $grading_status
     */
    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?int $position);

    /**
     * Remove a corrector assignment
     */
    public function removeAssignment(CorrectorAssignment $assignment);

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
        int $first_corrector_id,
        int $second_corrector_id,
        int $stitch_corrector_id,
        array $writer_ids,
        $dry_run = false
    ): array;

    /**
     * Assign correctors to empty corrector positions for the candidates
     * @return int number of new assignments
     */
    public function assignMissing(int $task_id): int;
}
