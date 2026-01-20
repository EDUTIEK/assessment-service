<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Writer;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as LogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\MentionUser as LogEntryMention;
use Edutiek\AssessmentService\Assessment\LogEntry\Type as LogEntryType;
use Edutiek\AssessmentService\Assessment\Pseudonym\FullService as PseudonymService;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterRemoved;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WorkingTimeFactory $working_time_factory,
        private LogEntryService $log_entry_service,
        private PseudonymService $pseudonym_service,
        private Dispatcher $events
    ) {
    }

    public function has(int $writer_id): bool
    {
        return $this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id);
    }

    public function getByUserId(int $user_id): Writer
    {
        $writer = $this->oneByUserId($user_id);
        if ($writer === null) {
            $writer = $this->repos->writer()->new()->setAssId($this->ass_id)->setUserId($user_id);
            // save to get an id for pseudonymisation
            $this->repos->writer()->save($writer);
            $writer->setPseudonym($this->pseudonym_service->buildForWriter($writer->getId(), $user_id));
            $this->repos->writer()->save($writer);
        } else {
            $this->checkScope($writer);
        }
        return $writer;
    }

    public function oneByUserId(int $user_id): ?Writer
    {
        return $this->repos->writer()->oneByUserIdAndAssId($user_id, $this->ass_id);
    }

    public function oneByWriterId(int $writer_id): ?Writer
    {
        $writer = $this->repos->writer()->one($writer_id);
        if ($writer) {
            $this->checkScope($writer);
        }
        return $writer;
    }

    public function all(): array
    {
        return $this->repos->writer()->allByAssId($this->ass_id);
    }

    /**
     * @todo: replace usage by dedicated operations and make private or remove
     */
    public function save(Writer $writer): void
    {
        $this->checkScope($writer);
        $this->repos->writer()->save($writer);
    }

    public function authorizeWriting(Writer $writer, int $by_user_id, bool $as_admin): void
    {
        $now = new DateTimeImmutable();

        $was_authorized = ($writer->getWritingAuthorized() !== null);
        if ($writer->getWorkingStart() === null) {
            $writer->setWorkingStart($now);
        }
        $writer->setWritingAuthorized($now);
        $writer->setWritingAuthorizedBy($by_user_id);
        if ($this->validate($writer)) {
            $this->save($writer);
            if ($as_admin) {
                $this->log_entry_service->addEntry(
                    LogEntryType::WRITING_POST_AUTHORIZED,
                    LogEntryMention::fromSystem($by_user_id),
                    LogEntryMention::fromWriter($writer)
                );
            }
        }
    }

    public function removeWritingAuthorization(Writer $writer, int $by_user_id): void
    {
        $was_authorized = ($writer->getWritingAuthorized() !== null);
        $writer->setWritingAuthorized(null);
        $writer->setWritingAuthorizedBy(null);
        if ($this->validate($writer)) {
            $this->save($writer);
            if ($was_authorized) {
                $this->log_entry_service->addEntry(
                    LogEntryType::WRITING_REMOVE_AUTHORIZATION,
                    LogEntryMention::fromSystem($by_user_id),
                    LogEntryMention::fromWriter($writer)
                );
            }
        }
    }

    public function removeCorrectionFinalisation(Writer $writer, int $by_user_id): void
    {
        $this->changeCorrectionStatus($writer, CorrectionStatus::OPEN, $by_user_id);
    }

    public function changeCorrectionStatus(Writer $writer, CorrectionStatus $status, int $by_user_id): void
    {
        $old_status = $writer->getCorrectionStatus();
        if ($status === CorrectionStatus::FINALIZED && in_array(
            $old_status,
            [CorrectionStatus::APPROXIMATION, CorrectionStatus::CONSULTING, CorrectionStatus::STITCH]
        )
        ) {
            $writer->setFinalizedFromStatus($old_status);
        } else {
            $writer->setFinalizedFromStatus(null);
        }
        $writer->setCorrectionStatus($status);
        $writer->setCorrectionStatusChanged(new \DateTimeImmutable("now"));
        $writer->setCorrectionStatusChangedBy($by_user_id);
        $this->save($writer);
        if ($old_status === CorrectionStatus::FINALIZED
            && $writer->getCorrectionStatus() !== CorrectionStatus::FINALIZED) {
            $this->log_entry_service->addEntry(
                LogEntryType::CORRECTION_REMOVE_AUTHORIZATION,
                LogEntryMention::fromSystem($by_user_id),
                LogEntryMention::fromWriter($writer)
            );
        }
    }

    public function changeWorkingTime(
        Writer $writer,
        ?\DateTimeImmutable $earliest_start,
        ?\DateTimeImmutable $latest_end,
        ?int $time_limit_minutes,
        int $by_user_id
    ): bool {
        $writer->setEarliestStart($earliest_start);
        $writer->setLatestEnd($latest_end);
        $writer->setTimeLimitMinutes($time_limit_minutes);
        if ($this->validate($writer)) {
            $this->save($writer);
            $this->log_entry_service->addEntry(
                LogEntryType::WORKING_TIME_CHANGE,
                LogEntryMention::fromSystem($by_user_id),
                LogEntryMention::fromWriter($writer)
            );
            return true;
        } else {
            return false;
        }
    }

    public function removeWorkingTime(Writer $writer, int $by_user_id): void
    {
        $writer->setEarliestStart(null);
        $writer->setLatestEnd(null);
        $writer->setTimeLimitMinutes(null);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WORKING_TIME_DELETE,
            LogEntryMention::fromSystem($by_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function repealExclusion(Writer $writer, int $by_user_id): void
    {
        $writer->setWritingExcluded(null);
        $writer->setWritingExcludedBy(null);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WRITER_REPEAL_EXCLUSION,
            LogEntryMention::fromSystem($by_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function exclude(Writer $writer, int $by_user_id): void
    {
        $writer->setWritingExcluded(new \DateTimeImmutable('now'));
        $writer->setWritingExcludedBy($by_user_id);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WRITER_EXCLUSION,
            LogEntryMention::fromSystem($by_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function remove(Writer $writer, ?int $by_user_id = null): void
    {
        $this->checkScope($writer);
        // TODO: Trigger removal of user data
        if ($by_user_id !== null) {
            $this->log_entry_service->addEntry(
                LogEntryType::WRITER_REMOVAL,
                LogEntryMention::fromSystem($by_user_id),
                LogEntryMention::fromWriter($writer)
            );
        }

        $this->repos->writer()->delete($writer->getId());
        $this->events->dispatchEvent(new WriterRemoved($writer->getId(), $this->ass_id));
    }

    private function checkScope(Writer $writer)
    {
        if ($writer->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }

    public function validate(Writer $writer): bool
    {
        $this->checkScope($writer);
        $settings = $this->repos->orgaSettings()->one($this->ass_id);
        $working_time = $this->working_time_factory->workingTime($settings, $writer);
        return $working_time->validate($writer);
    }

    public function hasStitchDecisions(): bool
    {
        return $this->repos->writer()->hasStitchDecisions($this->ass_id);
    }
}
