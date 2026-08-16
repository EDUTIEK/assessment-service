<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Data\AssignFilter;
use Edutiek\AssessmentService\Assessment\Data\AssignMode;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\Notification\DeliverService as NotificationService;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\Events\AssignmentRemoved;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as SpreadsheetService;
use Edutiek\AssessmentService\System\File\Delivery as FileDelivery;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\Api\Internal;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;
use Edutiek\AssessmentService\System\File\Disposition;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\System\Data\Result;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private CorrectionSettings $correction_settings,
        private CorrectorService $corrector_service,
        private WriterService $writer_service,
        private NotificationService $notification,
        private SpreadsheetService $spreadsheet_service,
        private LanguageService $lang,
        private FileDelivery $delivery,
        private FileStorage $storage,
        private Internal $internal,
        private Repositories $repos,
        private Dispatcher $events
    ) {
    }

    public function all(): array
    {
        return $this->repos->correctorAssignment()->allByAssId($this->ass_id);
    }

    public function allByWriterId(int $writer_id): array
    {
        return $this->repos->correctorAssignment()->allByWriterId($writer_id);
    }

    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array
    {
        return $this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id);
    }

    public function getMissingAssignmentsInfo(): ?string
    {
        $num_tasks = $this->repos->settings()->countByAssId($this->ass_id);
        $correctors_per_task = $this->correction_settings->getRequiredCorrectors();

        $correctable_ids = $this->writer_service->correctableIds();
        $required_normal = count($correctable_ids) * $num_tasks;
        $missing_first = $required_normal - $this->repos->correctorAssignment()->countByWriterIds($correctable_ids, [GradingPosition::FIRST]);

        $texts = [];
        if ($correctors_per_task === 1) {

            if ($missing_first > 0) {
                $texts[] = $missing_first == 1
                    ? $this->lang->txt('1_correction')
                    : $this->lang->txt('x_corrections', ['x' => $missing_first]);
            }
        } else {
            $stitchable_ids = $this->writer_service->stitchableIds();
            $require_stitch = count($stitchable_ids) * $num_tasks;

            $missing_second = $required_normal - $this->repos->correctorAssignment()->countByWriterIds($correctable_ids, [GradingPosition::SECOND]);
            $missing_stitch = $require_stitch - $this->repos->correctorAssignment()->countByWriterIds($stitchable_ids, [GradingPosition::STITCH]);

            if ($missing_first > 0) {
                $texts[] = $missing_first == 1
                    ? $this->lang->txt('1_first_correction')
                    : $this->lang->txt('x_first_corrections', ['x' => $missing_first]);
            }

            if ($missing_second > 0) {
                $texts[] = $missing_second == 1
                    ? $this->lang->txt('1_second_correction')
                    : $this->lang->txt('x_second_corrections', ['x' => $missing_second]);
            }

            if ($missing_stitch > 0) {
                $texts[] = $missing_stitch == 1
                    ? $this->lang->txt('1_stitch_decision')
                    : $this->lang->txt('x_stitch_decisions', ['x' => $missing_stitch]);
            }
        }


        return empty($texts) ? null : $this->lang->txt('assignments_missing') . ' ' . implode(', ', $texts);
    }

    public function allByCorrectorId(int $corrector_id, $only_authorized_writings = false): array
    {
        $assignments = $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);
        if ($only_authorized_writings) {
            $writer_ids = $this->writer_service->correctableIds();
            return array_filter(
                $assignments,
                fn(CorrectorAssignment $assignment) => in_array($assignment->getWriterId(), $writer_ids)
            );
        }
        return $assignments;
    }

    public function oneById(int $id): ?CorrectorAssignment
    {
        return $this->repos->correctorAssignment()->oneById($id);
    }

    public function oneByIds(int $writer_id, int $corrector_id, int $task_id): ?CorrectorAssignment
    {
        return $this->repos->correctorAssignment()->oneByIds($writer_id, $corrector_id, $task_id);
    }

    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?array $combined_status, ?int $position): void
    {
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $prefs->setFilterGradingStatus(
            $grading_status === null ? null : implode(
                ',',
                array_map(fn($status) => $status->value, $grading_status)
            )
        );

        $prefs->setFilterCombinedStatus(
            $combined_status === null ? null : implode(
                ',',
                array_map(fn($status) => $status->value, $combined_status)
            )
        );

        $prefs->setFilterAssignedPosition($position);

        $this->repos->correctorPrefs()->save($prefs);
    }

    public function getCorrectionFilter(int $corrector_id): array
    {
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $pos = $prefs->getFilterAssignedPosition();

        if ($prefs->getFilterGradingStatus() !== null) {
            $status = explode(',', $prefs->getFilterGradingStatus());
        }
        $status = empty($status) ? null : $status;

        if ($prefs->getFilterCombinedStatus() !== null) {
            $combined = explode(',', $prefs->getFilterCombinedStatus());
        }
        $combined = empty($combined) ? null : $combined;

        return [$status, $combined, $pos];
    }


    public function allByCorrectorIdFiltered(int $corrector_id, bool $only_authorized_writings = false): array
    {
        $assignments = $this->allByCorrectorId($corrector_id, $only_authorized_writings);

        [$status, $combined, $pos] = $this->getCorrectionFilter($corrector_id);

        $filtered = [];
        foreach ($assignments as $assignment) {
            if ($pos !== null && $assignment->getPosition()->value !== $pos) {
                continue;
            }
            if ($status !== null) {
                $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                    $assignment->getTaskId(),
                    $assignment->getWriterId(),
                    $assignment->getCorrectorId()
                );
                $value = $summary?->getGradingStatus()?->value ?? GradingStatus::NOT_STARTED->value;

                if (!in_array($value, $status)) {
                    continue;
                }
            }
            if ($combined !== null) {
                $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
                $value = $writer?->getCombinedStatus()?->value ?? CombinedStatus::WRITING_NOT_STARTED->value;

                if (!in_array($value, $combined)) {
                    continue;
                }
            }
            $filtered[] = $assignment;
        }

        return $filtered;
    }

    public function allForCorrectorAdminFiltered(): array
    {
        // todo: use filter from corrector administration
        $assignments = $this->all();
        $writer_ids = $this->writer_service->correctableIds();
        return array_filter(
            $assignments,
            fn(CorrectorAssignment $assignment) => in_array($assignment->getWriterId(), $writer_ids)
        );
    }

    public function removeAssignment(CorrectorAssignment $assignment): void
    {
        $this->repos->correctorAssignment()->delete($assignment->getId());

        $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        );

        // remove the authorization of all following corrector positions
        // if an authorized correction is removed
        if ($summary?->isAuthorized()) {
            foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId(
                $assignment->getTaskId(),
                $assignment->getWriterId()
            ) as $other) {
                if (GradingPosition::order($assignment->getPosition(), $other->getPosition()) > 0) {
                    $other_summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                        $other->getTaskId(),
                        $other->getWriterId(),
                        $other->getCorrectorId()
                    );
                    if ($other_summary?->isStarted()) {
                        $other_summary->setGradingStatus(GradingStatus::OPEN, $this->user_id);
                        $this->repos->correctorSummary()->save($other_summary);
                    }
                }
            }
        }

        // this will remove the correction data and set the correction status
        $this->events->dispatchEvent(new AssignmentRemoved(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId(),
            $assignment->getPosition()->isStitch(),
            $summary?->isAuthorized() ?? false
        ));
    }

    public function assignCorrectors(
        int $task_id,
        int $writer_id,
        int $first_corrector_id,
        int $second_corrector_id,
        int $stitch_corrector_id,
        $dry_run = false,
        $check_combination_only = false,
        $ignore_unchanged = false,
    ): Result {
        $corrector_ids = [
            0 => $first_corrector_id,
            1 => $second_corrector_id,
            2 => $stitch_corrector_id,
        ];

        /** @var CorrectorAssignment[] $old_assignments */
        /** @var CorrectorAssignment[] $new_assignments */
        $old_assignments = [];
        $new_assignments = [];
        foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            $old_assignments[$assignment->getPosition()->value] = $assignment;
        }

        /** @var CorrectorSummary[] $summaries */
        $summaries = [];
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, [$writer_id]) as $summary) {
            $summaries[$summary->getCorrectorId()] = $summary;
        }

        $change_any = false;
        $change_authorized = false;
        $ids = [];

        foreach ($corrector_ids as $position => $corrector_id) {
            $to_change = false;
            $old_assignment = $old_assignments[$position] ?? null;

            switch ($corrector_id) {
                case self::UNCHANGED_CORRECTOR_ASSIGNMENT:
                case $old_assignment?->getCorrectorId():
                    $new_assignment = $old_assignment;
                    break;

                case self::BLANK_CORRECTOR_ASSIGNMENT:
                    $new_assignment = null;
                    $to_change = $old_assignment !== null;
                    break;

                default:
                    if ($old_assignment !== null) {
                        $new_assignment = (clone $old_assignment)   // cloning is needed to prevent a change of cached objects
                        ->setCorrectorId($corrector_id);
                    } else {
                        $new_assignment = $this->repos->correctorAssignment()->new()
                            ->setTaskId($task_id)
                            ->setWriterId($writer_id)
                            ->setCorrectorId($corrector_id)
                            ->setPosition(GradingPosition::from($position));
                    }
                    $to_change = true;
            }

            $summary = $summaries[$old_assignment?->getCorrectorId()] ?? null;
            if ($to_change && $summary?->isAuthorized()) {
                $change_authorized = true;
            }

            $new_assignments[$position] = $new_assignment;
            if ($new_assignment?->getCorrectorId() !== null) {
                $ids[] = $new_assignment?->getCorrectorId();
            }
            $change_any = $change_any || $to_change;
        }

        $result = new Result(true);

        // check assignment combination
        if (count($ids) > 0 && count($ids) !== count(array_unique($ids))) {
            $result->addFailure($this->lang->txt('failure_corrector_assigned_twice'));
        }
        if ($check_combination_only) {
            return $result;
        }

        if ($change_authorized) {
            $result->addFailure($this->lang->txt('failure_change_assigment_of_authorized'));
        }
        if (!$change_any && !$ignore_unchanged) {
            $result->addFailure($this->lang->txt('failure_assignment_unchanged'));
        }

        if ($dry_run || $result->isFailed()) {
            return $result;
        }

        foreach (array_keys($corrector_ids) as $position) {
            $old_assignment = $old_assignments[$position] ?? null;
            $new_assignment = $new_assignments[$position] ?? null;

            if ($old_assignment !== null && $new_assignment !== null
                && $old_assignment->getCorrectorId() !== $new_assignment->getCorrectorId()
            ) {
                $this->moveCorrection(
                    $task_id,
                    $writer_id,
                    $old_assignment?->getCorrectorId(),
                    $new_assignment?->getCorrectorId()
                );
                // will overwrite the old assignment
                $this->repos->correctorAssignment()->save($new_assignment);
            } elseif ($old_assignment !== null && $new_assignment === null
            ) {
                $this->removeAssignment($old_assignment);

            } elseif ($new_assignment !== null) {
                $this->repos->correctorAssignment()->save($new_assignment);
            }

            if ($new_assignment?->getPosition() === GradingPosition::STITCH) {
                $writer = $this->writer_service->oneByWriterId($new_assignment->getWriterId());
                $corrector = $this->corrector_service->oneById($new_assignment->getCorrectorId());
                $this->notification->sendDirect(
                    NotificationType::CORRECTOR_STITCH_NEEDED,
                    [$corrector->getUserId()],
                    $writer
                );
            }

        } // next position

        return $result;
    }

    public function assignMissing(AssignFilter $filter, ?int $task_id): int
    {
        if ($task_id === null) {
            $task_ids = $this->repos->settings()->idsByAssId($this->ass_id);
        } else {
            if (!$this->repos->settings()->has($this->ass_id, $task_id)) {
                throw new ApiException('Wrong task_id given', ApiException::ID_SCOPE);
            }
            $task_ids = [$task_id];
        }

        $assigned = 0;
        foreach ($task_ids as $task_id) {
            switch ($this->correction_settings->getAssignMode()) {
                case AssignMode::RANDOM_EQUAL:
                default:
                    $assigned += $this->assignByRandomEqualMode($filter, $task_id);
            }
        }
        return $assigned;
    }

    public function exportAssignmentSpreadsheet(bool $only_authorized): void
    {
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id, $only_authorized);

        $writer_sheet = $this->spreadsheet_service->getNewSheet(
            $this->lang->txt('writer'),
            $ea->writerHeader(),
            $ea->writerBody()
        );
        $corrector_sheet = $this->spreadsheet_service->getNewSheet(
            $this->lang->txt('corrector'),
            $ea->correctorHeader(),
            $ea->correctorBody()
        );

        $file_id = $this->spreadsheet_service->sheetsToFile(
            [$writer_sheet, $corrector_sheet],
            ExportType::EXCEL,
            "corrector_assignment"
        );

        $this->delivery->sendFile($file_id, Disposition::ATTACHMENT);
        $this->storage->deleteFile($file_id);
    }

    public function importSpreadsheet(string $file_id): array
    {
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id, false);

        $data = $this->spreadsheet_service->dataFromFile($file_id, $this->lang->txt('writer'));
        return $assignments = $ea->importAssignments($data);
    }

    public function assignSpreadsheetData(array $data, bool $dry_run = false): array
    {
        $errors = [];
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id, false);

        if ($ea->isMultiTask()) {
            foreach ($data as $writer_id => $task_assignments) {
                foreach ($task_assignments as list($corrector_id, $pos, $task_id, $row_id)) {
                    $result = $this->assignCorrectors(
                        $task_id,
                        $writer_id,
                        $corrector_id ?? self::BLANK_CORRECTOR_ASSIGNMENT,
                        self::BLANK_CORRECTOR_ASSIGNMENT,
                        self::BLANK_CORRECTOR_ASSIGNMENT,
                        $dry_run,
                        false,
                        true
                    );
                    if ($result->isFailed()) {
                        $errors[] = sprintf($this->lang->txt('invalid_import_assignment'), $row_id)
                            . ': ' . implode('; ', $result->failures());
                    }
                }
            }
        } else {
            $task = current($this->repos->settings()->idsByAssId($this->ass_id));
            foreach ($data as $writer_id => $writer_assignments) {
                $first = $second = $stitch = self::BLANK_CORRECTOR_ASSIGNMENT;

                $row_id = null;
                foreach ($writer_assignments as list($corrector_id, $pos, $task_id, $row_id)) {
                    match($pos) {
                        GradingPosition::FIRST->value => $first = $corrector_id,
                        GradingPosition::SECOND->value => $second = $corrector_id,
                        GradingPosition::STITCH->value => $stitch = $corrector_id,
                    };
                    $task = $task_id;
                }

                $result = $this->assignCorrectors($task, $writer_id, $first, $second, $stitch, $dry_run, false, true);
                if ($result->isFailed()) {
                    $errors[] = sprintf($this->lang->txt('invalid_import_assignment'), $row_id)
                        . ': ' . implode('; ', $result->failures());
                }
            }
        }

        return array_merge($ea->getErrors(), $errors);
    }

    /**
     * Assign correctors randomly so that they get nearly equal number of corrections
     * @return int number of new assignments
     */
    private function assignByRandomEqualMode(AssignFilter $filter, int $task_id): int
    {
        if ($filter == AssignFilter::CORRECTABLE) {
            $writer_ids = $this->writer_service->correctableIds();
        } else {
            $writer_ids = $this->writer_service->allIds();
        }

        $position_values = array_map(
            fn(GradingPosition $p) => $p->value,
            GradingPosition::required($this->correction_settings->getRequiredCorrectors())
        );

        $assigned = 0;
        $writer_correctors = [];     // writer_id => [ position => corrector_id ]
        $corrector_writers = [];     // corrector_id => [ writer_id => position ]
        $corrector_pos_count = [];    // corrector_id => [ position => count ]

        // collect assignment data
        foreach ($this->corrector_service->all() as $corrector) {
            // init list of correctors with writers
            $corrector_writers[$corrector->getId()] = [];
            foreach ($position_values as $value) {
                $corrector_pos_count[$corrector->getId()][$value] = 0;
            }
        }
        foreach ($writer_ids as $writer_id) {

            // init list writers with correctors
            $writer_correctors[$writer_id] = [];

            foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
                if (in_array($assignment->getPosition()->value, $position_values)) {
                    // list the assigned corrector positions for each writer, give the corrector for each position
                    $writer_correctors[$assignment->getWriterId()][$assignment->getPosition()->value] = $assignment->getCorrectorId();
                    // list the assigned writers for each corrector, give the corrector position per writer
                    $corrector_writers[$assignment->getCorrectorId()][$assignment->getWriterId()] = $assignment->getPosition();
                    // count the assignments per position for a corrector
                    $corrector_pos_count[$assignment->getCorrectorId()][$assignment->getPosition()->value]++;
                }
            }
        }

        // assign empty corrector positions
        foreach ($writer_correctors as $writer_id => $corrector_by_pos) {
            foreach ($position_values as $value) {
                // empty corrector position
                if (!isset($corrector_by_pos[$value])) {

                    // collect the candidate corrector ids for the position
                    $candidates_by_count = [];
                    foreach ($corrector_writers as $corrector_id => $pos_by_writer_id) {

                        // corrector has not yet the writer assigned
                        if (!isset($pos_by_writer_id[$writer_id])) {
                            // group the candidates by their number of existing assignments for the position
                            $candidates_by_count[$corrector_pos_count[$corrector_id][$value]][] = $corrector_id;
                        }
                    }
                    if (!empty($candidates_by_count)) {

                        // get the candidate group with the smallest number of assignments for the position
                        ksort($candidates_by_count);
                        $candidate_ids = current($candidates_by_count);
                        $candidate_ids = array_unique($candidate_ids);

                        // get a random candidate id
                        shuffle($candidate_ids);
                        $corrector_id = current($candidate_ids);

                        // assign the corrector to the writer
                        $assignment = $this->repos->correctorAssignment()->new()
                            ->setTaskId($task_id)
                            ->setCorrectorId($corrector_id)
                            ->setWriterId($writer_id)
                            ->setPosition(GradingPosition::from($value));

                        $this->repos->correctorAssignment()->save($assignment);
                        $assigned++;

                        // remember the assignment for the next candidate collection
                        $corrector_writers[$corrector_id][$writer_id] = $value;
                        // not really needed, this fills the current empty corrector position
                        $writer_correctors[$writer_id][$value] = $corrector_id;
                        // increase the assignments per position for the corrector
                        $corrector_pos_count[$corrector_id][$value]++;
                    }
                }
            }
        }
        return $assigned;
    }

    private function moveCorrection(int $task_id, int $writer_id, int $from_corrector, int $to_corrector)
    {
        if ($from_corrector === $to_corrector) {
            // Prevent removal of criterion points and useless queries if nothing has changed
            return;
        }
        $this->repos->correctorSummary()->moveCorrectorByTaskIdAndWriterId($task_id, $writer_id, $from_corrector, $to_corrector);
        $this->repos->correctorPoints()->deleteByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $from_corrector);
        $this->repos->correctorComment()->moveCorrectorByTaskIdAndWriterId($task_id, $writer_id, $from_corrector, $to_corrector);
    }
}
