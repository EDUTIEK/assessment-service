<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Data\AssignMode;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\Events\AssignmentRemoved;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
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

    public function countMissingCorrectors(): int
    {
        $required = $this->correction_settings->getRequiredCorrectors();
        $count_assignments = [];
        array_map(
            fn (CorrectorAssignment $x) => $count_assignments[$x->getWriterId()] = 1 + $count_assignments[$x->getWriterId()] ?? 0,
            $this->repos->correctorAssignment()->allByAssId($this->ass_id)
        );

        $missing = 0;
        foreach ($this->writer_service->all() as $writer) {
            // get only writers with authorized essays without exclusion
            if ((empty($writer->getWritingAuthorized())) || !empty($writer->getWritingExcluded())) {
                continue;
            }
            $assigned = $count_assignments[$writer->getId()] ?? 0;
            $missing += max(0, $required - $assigned);
        }
        return $missing;
    }

    public function allByCorrectorId(int $corrector_id, $only_authorized_writings = false): array
    {
        $assignments = $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);
        if ($only_authorized_writings) {
            $writer_ids = $this->writer_service->correctableIds();
            return array_filter(
                $assignments,
                fn (CorrectorAssignment $assignment) => in_array($assignment->getWriterId(), $writer_ids)
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
                array_map(fn ($status) => $status->value, $grading_status)
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
            fn (CorrectorAssignment $assignment) => in_array($assignment->getWriterId(), $writer_ids)
        );
    }

    public function removeAssignment(CorrectorAssignment $assignment): void
    {
        // todo: check scope
        $this->repos->correctorAssignment()->delete($assignment->getId());
        $this->events->dispatchEvent(new AssignmentRemoved(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
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
                    $this->repos->correctorAssignment()->delete($old_assignment->getId());
                    $this->events->dispatchEvent(new AssignmentRemoved(
                        $old_assignment->getTaskId(),
                        $old_assignment->getWriterId(),
                        $old_assignment->getCorrectorId()
                    ));

                } elseif ($new_assignment !== null) {
                    $this->repos->correctorAssignment()->save($new_assignment);
                }
            } // next position
        } // next writer

        return $result;
    }

    public function assignMissing(int $task_id): int
    {
        switch ($this->correction_settings->getAssignMode()) {
            case AssignMode::RANDOM_EQUAL:
            default:
                return $this->assignByRandomEqualMode($task_id);
        }
    }

    public function exportAssignmentSpreadsheet(bool $only_authorized): void
    {
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id);

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

        $file_id = $this->spreadsheet_service->sheetsToFile([$writer_sheet, $corrector_sheet], ExportType::EXCEL);

        $this->delivery->sendFile(
            $file_id,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()
                          ->setFileName("corrector_assignment" . ExportType::EXCEL->extension())
                          ->setMimeType(ExportType::EXCEL->mimetype())
        );
        $this->storage->deleteFile($file_id);
    }

    public function importSpreadsheet(string $file_id): array
    {
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id);

        $data = $this->spreadsheet_service->dataFromFile($file_id, $this->lang->txt('writer'));
        return $assignments = $ea->importAssignments($data);
    }

    public function assignSpreadsheetData(array $data, bool $dry_run = false): array
    {
        $errors = [];
        $ea = $this->internal->excelAssignmentData($this->ass_id, $this->user_id);

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
    private function assignByRandomEqualMode(int $task_id): int
    {
        $required = $this->correction_settings->getRequiredCorrectors();
        if ($required < 1) {
            return 0;
        }

        $assigned = 0;
        $writerCorrectors = [];     // writer_id => [ position => $corrector_id ]
        $correctorWriters = [];     // corrector_id => [ writer_id => position ]
        $correctorPosCount = [];    // corrector_id => [ position => count ]

        // collect assignment data
        foreach ($this->corrector_service->all() as $corrector) {
            // init list of correctors with writers
            $correctorWriters[$corrector->getId()] = [];
            for ($position = 0; $position < $required; $position++) {
                $correctorPosCount[$corrector->getId()][$position] = 0;
            }
        }
        foreach ($this->writer_service->all() as $writer) {

            // get only not authorized writers not excluded
            if (empty($writer->getWritingAuthorized()) || !empty($writer->getWritingExcluded())) {
                continue;
            }

            // init list writers with correctors
            $writerCorrectors[$writer->getId()] = [];

            foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer->getId()) as $assignment) {
                // list the assigned corrector positions for each writer, give the corrector for each position
                $writerCorrectors[$assignment->getWriterId()][$assignment->getPosition()->value] = $assignment->getCorrectorId();
                // list the assigned writers for each corrector, give the corrector position per writer
                $correctorWriters[$assignment->getCorrectorId()][$assignment->getWriterId()] = $assignment->getPosition();
                // count the assignments per position for a corrector
                $correctorPosCount[$assignment->getCorrectorId()][$assignment->getPosition()->value]++;
            }
        }

        // assign empty corrector positions
        foreach ($writerCorrectors as $writerId => $correctorsByPos) {
            for ($position = 0; $position < $required; $position++) {
                // empty corrector position
                if (!isset($correctorsByPos[$position])) {

                    // collect the candidate corrector ids for the position
                    $candidatesByCount = [];
                    foreach ($correctorWriters as $correctorId => $posByWriterId) {

                        // corrector has not yet the writer assigned
                        if (!isset($posByWriterId[$writerId])) {
                            // group the candidates by their number of existing assignments for the position
                            $candidatesByCount[$correctorPosCount[$correctorId][$position]][] = $correctorId;
                        }
                    }
                    if (!empty($candidatesByCount)) {

                        // get the candidate group with the smallest number of assignments for the position
                        ksort($candidatesByCount);
                        reset($candidatesByCount);
                        $candidateIds = current($candidatesByCount);
                        $candidateIds = array_unique($candidateIds);

                        // get a random candidate id
                        shuffle($candidateIds);
                        $correctorId = current($candidateIds);

                        // assign the corrector to the writer
                        $assignment = $this->repos->correctorAssignment()->new()
                            ->setTaskId($task_id)
                            ->setCorrectorId($correctorId)
                            ->setWriterId($writerId)
                            ->setPosition(GradingPosition::tryFrom($position)) ?? GradingPosition::FIRST;

                        $this->repos->correctorAssignment()->save($assignment);
                        $assigned++;

                        // remember the assignment for the next candidate collection
                        $correctorWriters[$correctorId][$writerId] = $position;
                        // not really needed, this fills the current empty corrector position
                        $writerCorrectors[$writerId][$position] = $correctorId;
                        // increase the assignments per position for the corrector
                        $correctorPosCount[$correctorId][$position]++;
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
