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

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private CorrectionSettings $correction_settings,
        private CorrectorService $corrector_service,
        private WriterService $writer_service,
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
            fn(CorrectorAssignment $x) => $count_assignments[$x->getWriterId()] = 1 + $count_assignments[$x->getWriterId()] ?? 0,
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

    public function allByCorrectorId(int $corrector_id): array
    {
        return $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);
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

    public function allByCorrectorIdFiltered(int $corrector_id): array
    {
        $assignments = $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);

        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $pos = $prefs->getFilterAssignedPosition();
        $status = null;
        if ($prefs->getFilterGradingStatus() !== null) {
            $status = explode(',', $prefs->getFilterGradingStatus());
        }

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

    public function removeAssignment(CorrectorAssignment $assignment): void
    {
        // todo: check scope
        $this->repos->correctorAssignment()->delete($assignment->getId());
        $this->events->dispatchEvent(new AssignmentRemoved(
            $assignment->getTaskId(),
            $assignment->getCorrectorId(),
            $assignment->getCorrectorId()
        ));
    }

    public function assignMultiple(
        int $task_id,
        int $first_corrector,
        int $second_corrector,
        array $writer_ids,
        $dry_run = false
    ): array {
        $assignments = [];
        foreach ($this->repos->correctorAssignment()->allByTaskId($task_id) as $assignment) {
            $assignments[$assignment->getWriterId()][$assignment->getPosition()->value] = $assignment;
        }
        $summaries = $this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, $writer_ids);

        $result = ["changed" => [], "unchanged" => [], "invalid" => []];

        foreach ($writer_ids as $writer_id) {
            $first_assignment = $assignments[$writer_id][0] ?? null;
            $second_assignment = $assignments[$writer_id][1] ?? null;
            $old_first_corrector = $first_assignment?->getCorrectorId();
            $old_second_corrector = $second_assignment?->getCorrectorId();

            $first_summary = $first_assignment !== null ?
                ($summaries[$writer_id][$first_assignment->getCorrectorId()] ?? null) : null;
            $second_summary = $second_assignment !== null ?
                ($summaries[$writer_id][$second_assignment->getCorrectorId()] ?? null) : null;

            $first_unchanged = $this->assign($task_id, $writer_id, $first_corrector, $first_assignment, $first_summary, 0); // assignment is changed by reference
            $second_unchanged = $this->assign($task_id, $writer_id, $second_corrector, $second_assignment, $second_summary, 1); // assignment is changed by reference

            // Do nothing if both are unchanged
            if ($first_unchanged && $second_unchanged) {
                $result["unchanged"][] = $writer_id;
            } elseif ($first_assignment !== null
                && $second_assignment !== null
                && $first_assignment->getCorrectorId() == $second_assignment->getCorrectorId()
            ) {// Do not proceed if first and second position is the same
                $result["invalid"][] = $writer_id;
            } else {

                $result["changed"][] = $writer_id;
                if (!$dry_run) {// Stop here if it's a dry run

                    if ($old_first_corrector !== null && $first_assignment !== null && $first_summary !== null) {
                        // Move all comments and summaries of first correction to new corrector if they changed,
                        // criterium points are individual and are removed
                        $this->moveCorrection($task_id, $old_first_corrector, $first_assignment->getCorrectorId(), $first_summary->getEssayId());
                    }

                    if ($old_second_corrector !== null && $second_assignment !== null && $second_summary !== null) {
                        // Move all comments and summaries of second correction to new corrector if they changed,
                        // criterium points are individual and are removed
                        $this->moveCorrection(
                            $task_id,
                            $second_assignment->getWriterId(),
                            $old_second_corrector,
                            $second_assignment->getCorrectorId(),
                        );
                    }

                    if ($first_assignment === null && $old_first_corrector !== null && $first_summary !== null) {
                        // if the first assignment is removed, also its comments and summary are removed
                        $this->deleteCorrection($task_id, $first_summary->getWriterId(), $old_first_corrector);
                    }

                    if ($second_assignment === null && $old_second_corrector !== null && $second_summary !== null) {
                        // if the second assignment is removed, also its comments and summary are removed
                        $this->deleteCorrection($task_id, $second_summary->getWriterId(), $old_second_corrector);
                    }

                    // If something changed remove old assignments
                    $this->repos->correctorAssignment()->deleteByTaskIdAndWriterId($task_id, $writer_id);

                    if ($first_assignment !== null) {
                        $this->repos->correctorAssignment()->save($first_assignment);
                    }

                    if ($second_assignment !== null) {
                        $this->repos->correctorAssignment()->save($second_assignment);
                    }
                }
            }

        }
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

    private function assign(int $task_id, int $writer_id, int $corrector, ?CorrectorAssignment &$assignment, ?CorrectorSummary $summary, int $position): bool
    {
        $unchanged = true;
        $authorized = isset($summary) && $summary->getCorrectionAuthorized() !== null;

        if ($corrector > -1) {// corrector is real and not removed or keep unchanged
            if ($assignment == null) { // if assignment is missing create a new
                $assignment = $this->repos->correctorAssignment()->new()
                    ->setTaskId($task_id)
                    ->setWriterId($writer_id)
                    ->setCorrectorId($corrector)
                    ->setPosition(GradingPosition::from($position));

                $unchanged = false;
            } elseif ($assignment->getCorrectorId() != $corrector && !$authorized) { // if corrector is changed assign new
                $assignment = clone $assignment; // cloning is needed to prevent the usage of cached objects
                $assignment->setCorrectorId($corrector);
                $unchanged = false;
            }
        }
        if ($corrector == self::BLANK_CORRECTOR_ASSIGNMENT && !$authorized) {// corrector assignment is actively removed
            $assignment = null;
            $unchanged = false;
        }
        return $unchanged;
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
                $writerCorrectors[$assignment->getWriterId()][$assignment->getPosition()] = $assignment->getCorrectorId();
                // list the assigned writers for each corrector, give the corrector position per writer
                $correctorWriters[$assignment->getCorrectorId()][$assignment->getWriterId()] = $assignment->getPosition();
                // count the assignments per position for a corrector
                $correctorPosCount[$assignment->getCorrectorId()][$assignment->getPosition()]++;
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
            return;
        }//Prevent removal of criterion points and useless queries if nothing has changed
        $this->repos->correctorSummary()->moveCorrectorByTaskIdAndWriterId($task_id, $writer_id, $from_corrector, $to_corrector);
        $this->repos->correctorPoints()->deleteByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $from_corrector);
        $this->repos->correctorComment()->moveCorrectorByTaskIdAndWriterId($task_id, $writer_id, $from_corrector, $to_corrector);
    }

    private function deleteCorrection(int $task_id, int $writer_id, int $corrector)
    {
        $this->repos->correctorSummary()->deleteByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $corrector);
        $this->repos->correctorComment()->deleteByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $corrector);
        $this->repos->correctorPoints()->deleteByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $corrector);
    }
}
