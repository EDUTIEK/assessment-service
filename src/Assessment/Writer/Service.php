<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\WorkingTime\Service as WorkingTimeService;
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as LogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\Type as LogEntryType;
use Edutiek\AssessmentService\Assessment\LogEntry\MentionUser as LogEntryMention;
use Edutiek\AssessmentService\Assessment\LogEntry\Type;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WorkingTimeFactory $working_time_factory,
        private LogEntryService $log_entry_service
    ) {
    }

    public function has(int $writer_id): bool
    {
        return $this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id);
    }

    public function getByUserId(int $user_id) : Writer
    {
        $writer = $this->oneByUserId($user_id);
        if ($writer === null) {
            $writer = $this->repos->writer()->new()->setAssId($this->ass_id)->setUserId($user_id);
            $this->repos->writer()->save($writer);
        } else {
            $this->checkScope($writer);
        }
        return $writer;
    }

    public function oneByUserId(int $user_id) : ?Writer
    {
        return $this->repos->writer()->oneByUserIdAndAssId($user_id, $this->ass_id);
    }

    public function oneByWriterId(int $writer_id): ?Writer
    {
        $writer = $this->repos->writer()->one($writer_id);
        $this->checkScope($writer);
        return $writer;
    }

    public function all(): array
    {
        return $this->repos->writer()->allByAssId($this->ass_id);
    }

    public function save(Writer $writer): void
    {
        $this->checkScope($writer);
        $this->repos->writer()->save($writer);
    }

    public function changeWorkingTime(
        Writer $writer,
        ?\DateTimeImmutable $earliest_start, ?\DateTimeImmutable $latest_end, ?int $time_limit_minutes,
        int $from_user_id
    ) : bool
    {
        $writer->setEarliestStart($earliest_start);
        $writer->setLatestEnd($latest_end);
        $writer->setTimeLimitMinutes($time_limit_minutes);
        if($this->validate($writer)) {
            $this->save($writer);
            $this->log_entry_service->addEntry(
                LogEntryType::WORKING_TIME_CHANGE,
                LogEntryMention::fromSystem($from_user_id),
                LogEntryMention::fromWriter($writer)
            );
            return true;
        } else {
            return false;
        }
    }

    public function removeWorkingTime(Writer $writer, int $from_user_id) : void
    {
        $writer->setEarliestStart(null);
        $writer->setLatestEnd(null);
        $writer->setTimeLimitMinutes(null);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WORKING_TIME_DELETE,
            LogEntryMention::fromSystem($from_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function repealExclusion(Writer $writer, int $from_user_id) : void
    {
        $writer->setWritingExcluded(null);
        $writer->setWritingExcludedBy(null);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WRITER_REPEAL_EXCLUSION,
            LogEntryMention::fromSystem($from_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function exclude(Writer $writer, int $from_user_id) : void
    {
        $writer->setWritingExcluded(new \DateTimeImmutable('now'));
        $writer->setWritingExcludedBy($from_user_id);
        $this->save($writer);
        $this->log_entry_service->addEntry(
            LogEntryType::WRITER_EXCLUSION,
            LogEntryMention::fromSystem($from_user_id),
            LogEntryMention::fromWriter($writer)
        );
    }

    public function remove(Writer $writer, ?int $from_user_id = null) : void
    {
        $this->checkScope($writer);
        // TODO: Trigger removal of user data
        if ($from_user_id !== null) {
            $this->log_entry_service->addEntry(
                LogEntryType::WRITER_REMOVAL,
                LogEntryMention::fromSystem($from_user_id),
                LogEntryMention::fromWriter($writer)
            );
        }

        $this->repos->writer()->delete($writer->getId());
    }

    private function checkScope(Writer $writer)
    {
        if ($writer->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }

    public function validate(Writer $writer) : bool
    {
        $this->checkScope($writer);
        $settings = $this->repos->orgaSettings()->one($this->ass_id);
        $working_time = $this->working_time_factory->workingTime($settings, $writer);
        return $working_time->validate($writer);
    }
}