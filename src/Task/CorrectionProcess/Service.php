<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingStatus;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentsService;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as CorrectionProcessService;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\Type as LogEntryType;
use Edutiek\AssessmentService\Assessment\LogEntry\MentionUser;
use Edutiek\AssessmentService\Assessment\LogEntry\Type;
use Edutiek\AssessmentService\Assessment\Data\AssignMode;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WriterService $writer_service,
        private CorrectionProcessService $correction_process,
        private LogEntryService $log_entry,
        private CorrectionSettings $correction_settings,
        private CorrectorService $corrector_service
    ) {
    }

    public function authorizeCorrection(CorrectorSummary $summary, int $user_id): void
    {
        //$settings = $this->getSettings();
        //$preferences = $this->correctorRepo->getCorrectorPreferences($summary->getCorrectorId());
        //$summary->applySettingsOrPreferences($settings, $preferences); //TODO needs to be reattached after Settings are recomitted

        if (empty($summary->getCorrectionAuthorized())) {
            $summary->setCorrectionAuthorized($summary->getLastChange() ?? new \DateTimeImmutable("now"));
            $summary->setCorrectionAuthorizedBy($user_id);
        }
        if (empty($summary->getCorrectionAuthorizedBy())) {
            $summary->setCorrectionAuthorizedBy($user_id);
        }

        $this->repos->correctorSummary()->save($summary);
    }

    public function removeAuthorizations(int $task_id, Writer $writer, int $user_id): bool
    {
        $changed = false;

        // remove finalized status
        $this->correction_process->removeFinalization($writer);

        // remove authorizations
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, [$writer->getId()]) as $summary) {
            $summary->setCorrectionAuthorized(null);
            $summary->setCorrectionAuthorizedBy(null);
            $this->repos->correctorSummary()->save($summary);
            $changed = true;
        }

        if ($changed) {
            $this->log_entry->addEntry(
                LogEntryType::CORRECTION_REMOVE_AUTHORIZATION, MentionUser::fromSystem($user_id), MentionUser::fromWriter($writer));
        }

        return $changed;
    }

    public function removeCorrectorAuthorizations(Corrector $corrector, int $user_id): bool
    {
        if (empty($summaries = $this->repos->correctorSummary()->allByCorrectorId($corrector->getId()))) {
            return false;
        }
        $writers = [];

        foreach($this->writer_service->all() as $writer) {
            $writers[$writer->getId()] = $writer;
        }

        foreach($summaries as $summary) {
            $writer = $writers[$summary->getWriterId()] ?? null;
            // don't remove a singe authorization from a finalized correction
            if (empty($writer) || !empty($writer->getCorrectionFinalized())) {
                continue;
            }

            $summary->setCorrectionAuthorized(null);
            $summary->setCorrectionAuthorizedBy(null);
            $this->repos->correctorSummary()->save($summary);
            $this->log_entry->addEntry(LogEntryType::CORRECTION_REMOVE_OWN_AUTHORIZATION, MentionUser::fromSystem($user_id), MentionUser::fromWriter($writer));
        }

        return true;
    }


    public function assignMultiple(
        int $task_id,
        int $first_corrector,
        int $second_corrector,
        array $writer_ids,
        $dry_run = false): array
    {
        $assignments = [];
        foreach($this->repos->correctorAssignment()->allByTaskId($task_id) as $assignment) {
            $assignments[$assignment->getWriterId()][$assignment->getPosition()] = $assignment;
        }
        $summaries = $this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, $writer_ids);

        $result = ["changed" => [], "unchanged" => [], "invalid" => []];

        foreach ($writer_ids as $writer_id) {
            $first_assignment = $assignments[$writer_id][0] ?? null;
            $second_assignment = $assignments[$writer_id][1] ?? null;
            $old_first_corrector = $first_assignment !== null
                ? $first_assignment->getCorrectorId()
                : null;
            $old_second_corrector = $second_assignment !== null ?
                $second_assignment->getCorrectorId() : null;

            $first_summary =  $first_assignment !== null ?
                ($summaries[$writer_id][$first_assignment->getCorrectorId()] ?? null) : null;
            $second_summary = $second_assignment !== null ?
                ($summaries[$writer_id][$second_assignment->getCorrectorId()] ?? null) : null;

            $first_unchanged = $this->assign($task_id, $writer_id, $first_corrector, $first_assignment, $first_summary, 0); // assignment is changed by reference
            $second_unchanged = $this->assign($task_id, $writer_id, $second_corrector, $second_assignment, $second_summary, 1); // assignment is changed by reference

            // Do nothing if both are unchanged
            if($first_unchanged && $second_unchanged) {
                $result["unchanged"][] = $writer_id;
            } elseif($first_assignment !== null
                && $second_assignment !== null
                && $first_assignment->getCorrectorId() == $second_assignment->getCorrectorId()
            ) {// Do not proceed if first and second position is the same
                $result["invalid"][] = $writer_id;
            } else {

                $result["changed"][] = $writer_id;
                if(!$dry_run) {// Stop here if it's a dry run

                    if($old_first_corrector !== null && $first_assignment !== null && $first_summary !== null) {
                        // Move all comments and summaries of first correction to new corrector if they changed,
                        // criterium points are individual and are removed
                        $this->moveCorrection($task_id, $old_first_corrector, $first_assignment->getCorrectorId(), $first_summary->getEssayId());
                    }

                    if($old_second_corrector !== null && $second_assignment !== null && $second_summary !== null) {
                        // Move all comments and summaries of second correction to new corrector if they changed,
                        // criterium points are individual and are removed
                        $this->moveCorrection(
                            $task_id,
                            $second_assignment->getWriterId(),
                            $old_second_corrector,
                            $second_assignment->getCorrectorId(),
                        );
                    }

                    if($first_assignment === null && $old_first_corrector !== null && $first_summary !== null) {
                        // if the first assignment is removed, also its comments and summary are removed
                        $this->deleteCorrection($task_id, $first_summary->getWriterId(), $old_first_corrector);
                    }

                    if($second_assignment === null && $old_second_corrector !== null  && $second_summary !== null) {
                        // if the second assignment is removed, also its comments and summary are removed
                        $this->deleteCorrection($task_id, $second_summary->getWriterId(), $old_second_corrector);
                    }

                    // If something changed remove old assignments
                    $this->repos->correctorAssignment()->deleteByTaskIdAndWriterId($task_id, $writer_id);

                    if($first_assignment !== null) {
                        $this->repos->correctorAssignment()->save($first_assignment);
                    }

                    if($second_assignment !== null) {
                        $this->repos->correctorAssignment()->save($second_assignment);
                    }
                }
            }

        }
        return $result;
    }

    private function moveCorrection(int $task_id, int $writer_id, int $from_corrector, int $to_corrector)
    {
        if($from_corrector === $to_corrector) {
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

    private function assign(int $task_id, int $writer_id, int $corrector, ?CorrectorAssignment &$assignment, ?CorrectorSummary $summary, int $position) : bool
    {
        $unchanged = true;
        $authorized = isset($summary) && $summary->getCorrectionAuthorized() !== null;

        if($corrector > -1) {// corrector is real and not removed or keep unchanged
            if ($assignment == null) { // if assignment is missing create a new
                $assignment = $this->repos->correctorAssignment()->new()
                                          ->setTaskId($task_id)
                                          ->setWriterId($writer_id)
                                          ->setCorrectorId($corrector)
                                          ->setPosition($position);

                $unchanged = false;
            } elseif ($assignment->getCorrectorId() != $corrector && !$authorized) { // if corrector is changed assign new
                $assignment = clone $assignment; // cloning is needed to prevent the usage of cached objects
                $assignment->setCorrectorId($corrector);
                $unchanged = false;
            }
        }
        if($corrector == self::BLANK_CORRECTOR_ASSIGNMENT && !$authorized) {// corrector assignment is actively removed
            $assignment = null;
            $unchanged = false;
        }
        return $unchanged;
    }

    public function assignMissing(int $task_id) : int
    {
        switch ($this->correction_settings->getAssignMode()) {
            case AssignMode::RANDOM_EQUAL:
            default:
                return $this->assignByRandomEqualMode($task_id);
        }
    }

    /**
     * Assign correctors randomly so that they get nearly equal number of corrections
     * @return int number of new assignments
     */
    protected function assignByRandomEqualMode(int $task_id) : int
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

            foreach($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer->getId()) as $assignment) {
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
                                                  ->setPosition($position);

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
}
