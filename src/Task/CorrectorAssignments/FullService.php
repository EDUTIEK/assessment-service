<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Assessment\Data\AssignFilter;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\System\Data\Result;

interface FullService extends ReadService
{
    public const BLANK_CORRECTOR_ASSIGNMENT = -1;
    public const UNCHANGED_CORRECTOR_ASSIGNMENT = -2;

    /**
     * Get the current correction filter set by a corrector
     * @return array [?array $grading_status, ?array $combined_status, ?int $position]
     */
    public function getCorrectionFilter(int $corrector_id): array;

    /**
     * Save a filter for showing assignments to a corrector
     * This is set on the start page of a corrector
     * This is used to filter the assigned items in the corrector app
     *
     * @param GradingStatus[]|null $grading_status
     * @param CombinedStatus[]|null $combined_status
     */
    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?array $combined_status, ?int $position);

    /**
     * Remove a corrector assignment
     * This triggers an AssignmentRemoved event to delete all assigned correction data
     */
    public function removeAssignment(CorrectorAssignment $assignment);

    /**
     * (Re-)Assign correctors to a writer
     * The result will be failed if:
     * - a corrector is assigned twice,
     * - an assignment to change has an authorized correction,
     * - all assignments are untouched
     *
     * Existing correction summaries and comments are moved to the new corrector .
     * If the assignment of an existing correction is removed, the summaries and comments are removed too!
     * Criterion points are removed if an existing correction is changed or removed because they can be individual
     *   and not reused by the new assigned corrector
     */
    public function assignCorrectors(
        int $task_id,
        int $writer_id,
        int $first_corrector_id,
        int $second_corrector_id,
        int $stitch_corrector_id,
        $dry_run = false,
        $check_combination_only = false,
        $ignore_unchanged = false
    ): Result;

    /**
     * Assign correctors to empty corrector positions for the candidates
     * @return int number of new assignments
     */
    public function assignMissing(AssignFilter $filter, ?int $task_id): int;

    /**
     * Export file with writer and its assigned correctors
     * @return string
     */
    public function exportAssignmentSpreadsheet(bool $only_authorized): void;

    /**
     * Import assignments from a spreadsheet created with self::exportAssignmentSpreadsheet
     * @return array Data
     */
    public function importSpreadsheet(string $file_id): array;

    /**
     * Assign correctors to writers from spreadsheet data created with self::importSpreadsheet
     * @return string[] error strings, good if empty
     */
    public function assignSpreadsheetData(array $data, bool $dry_run = false): array;

}
