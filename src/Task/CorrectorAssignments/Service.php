<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Data\AssignFilter;
use Edutiek\AssessmentService\Assessment\Data\AssignMode;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
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

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private CorrectionSettings $correction_settings,
        private CorrectorService $corrector_service,
        private WriterService $writer_service,
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

    public function countMissingAssignments(): int
    {
        $correctable_ids = $this->writer_service->correctableIds();

        $num_tasks = $this->repos->settings()->countByAssId($this->ass_id);
        $correctors_per_task = $this->correction_settings->getRequiredCorrectors();

        $required = count($correctable_ids) * $num_tasks * $correctors_per_task;
        $assigned = $this->repos->correctorAssignment()->countByWriterIds($correctable_ids);

        return max(0, $required - $assigned);
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

    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?int $position): void
    {
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $prefs->setFilterGradingStatus(
            $grading_status === null ? null : implode(
                ',',
                array_map(fn($status) => $status->value, $grading_status)
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
        $status = null;
        if ($prefs->getFilterGradingStatus() !== null) {
            $status = explode(',', $prefs->getFilterGradingStatus());
        }
        return [$status, $pos];
    }


    public function allByCorrectorIdFiltered(int $corrector_id, bool $only_authorized_writings = false): array
    {
        $assignments = $this->allByCorrectorId($corrector_id, $only_authorized_writings);

        [$status, $pos] = $this->getCorrectionFilter($corrector_id);

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
        $reset_status = true;

        $this->repos->correctorAssignment()->delete($assignment->getId());

        $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        );

        // reset a writer's correction status only if an authorized correction is unassigned
        $reset_status = $summary?->isAuthorized() ?? false;

        $this->events->dispatchEvent(new AssignmentRemoved(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId(),
            $reset_status
        ));
    }

    /**
     * Assign correctors to multiple writers
     */
    public function assignMultiple(
        int $task_id,
        int $first_corrector_id,
        int $second_corrector_id,
        int $stitch_corrector_id,
        array $writer_ids,
        $dry_run = false
    ): array {
        $corrector_ids = [
            0 => $first_corrector_id,
            1 => $second_corrector_id,
            2 => $stitch_corrector_id,
        ];

        $result = ["changed" => [], "unchanged" => [], "invalid" => []];

        /** @var CorrectorAssignment[][] $old_assignments */
        /** @var CorrectorAssignment[][] $new_assignments */
        $old_assignments = [];
        $new_assignments = [];
        foreach ($this->repos->correctorAssignment()->allByTaskId($task_id) as $assignment) {
            $old_assignments[$assignment->getWriterId()][$assignment->getPosition()->value] = $assignment;
        }

        /** @var CorrectorSummary[][] $summaries */
        $summaries = [];
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, $writer_ids) as $summary) {
            $summaries[$summary->getCorrectorId()][$summary->getCorrectorId()] = $summary;
        }

        foreach ($writer_ids as $writer_id) {

            $change_any = false;
            $ids = [];

            foreach ($corrector_ids as $position => $corrector_id) {
                $to_change = false;

                $old_assignment = $old_assignments[$writer_id][$position] ?? null;
                $summary = $summaries[$writer_id][$old_assignment?->getCorrectorId()] ?? null;

                if ($summary?->isAuthorized()) {
                    $new_assignment = $old_assignment;
                } else {
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
                }

                $new_assignments[$writer_id][$position] = $new_assignment;
                if ($new_assignment?->getCorrectorId() !== null) {
                    $ids[] = $new_assignment?->getCorrectorId();
                }
                $change_any = $change_any || $to_change;
            }

            // Do not proceed if a corrector is assigned twice
            if (count($ids) > 0 && count($ids) !== count(array_unique($ids))) {
                $result["invalid"][] = $writer_id;
                continue; // next writer
            }
            if ($change_any) {
                $result["changed"][] = $writer_id;
            } else {
                $result["unchanged"][] = $writer_id;
            }

            if ($dry_run) {
                continue; // next writer
            }

            foreach (array_keys($corrector_ids) as $position) {
                $old_assignment = $old_assignments[$writer_id][$position] ?? null;
                $new_assignment = $new_assignments[$writer_id][$position] ?? null;

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
            } // next position
        } // next writer

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
                    $result = $this->assignMultiple(
                        $task_id,
                        $corrector_id ?? self::BLANK_CORRECTOR_ASSIGNMENT,
                        self::BLANK_CORRECTOR_ASSIGNMENT,
                        self::BLANK_CORRECTOR_ASSIGNMENT,
                        [$writer_id],
                        $dry_run
                    );
                    if (!empty($result['invalid'])) {
                        $errors[] = sprintf(
                            $this->lang->txt('invalid_import_assignment'),
                            $row_id
                        );
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

                $result = $this->assignMultiple($task, $first, $second, $stitch, [$writer_id], $dry_run);
                if (!empty($result['invalid'])) {
                    $errors[] = sprintf(
                        $this->lang->txt('invalid_import_assignment'),
                        $row_id
                    );
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
