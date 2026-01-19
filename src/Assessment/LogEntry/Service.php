<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\Service as UserService;

class Service implements FullService
{
    public function __construct(
        private readonly int $ass_id,
        private Repositories $repos,
        private LanguageService $language_service,
        private UserService $user_service,
    ) {

    }

    private function getNameFromMention(?MentionUser $mention) : string
    {
        $user_data = null;

        $id = match($mention?->type){
            UserType::Writer => $this->repos->corrector()->one($mention->id)?->getUserId(),
            UserType::Corrector => $this->repos->writer()->one($mention->id)?->getUserId(),
            UserType::System => $mention->id,
            null => null
        };
        $user_data = $id !== null ? $this->user_service->getUser($id) : null;

        return $user_data?->getListname(true) ?? $this->language_service->txt('unknown');
    }

    public function addEntry(
        Type $type,
        ?MentionUser $subject_mention,
        ?MentionUser $object_mention = null,
        ?string $note = null
    ): void {
        $timestamp = new \DateTimeImmutable('now');
        $category = $type->getCategory();

        $subject = $this->getNameFromMention($subject_mention);
        $object = $this->getNameFromMention($object_mention);

        $entry = match($type){
            Type::WORKING_TIME_CHANGE => sprintf($this->language_service->txt('log_entry_working_time_changed'), $object, $subject),
            Type::WORKING_TIME_DELETE => sprintf(($this->language_service->txt('log_entry_working_time_deleted')), $object, $subject),
            TYPE::WRITER_EXCLUSION => sprintf(($this->language_service->txt('log_entry_writer_exclusion')), $object, $subject),
            TYPE::WRITER_REPEAL_EXCLUSION => sprintf($this->language_service->txt('log_entry_writer_repealed_exclusion'), $object, $subject),
            TYPE::WRITER_REMOVAL => sprintf(($this->language_service->txt('log_entry_writer_removal')), $object, $subject),
            TYPE::WRITING_POST_AUTHORIZED => sprintf($this->language_service->txt('log_entry_writing_post_authorized'), $object, $subject),
            TYPE::WRITING_REMOVE_AUTHORIZATION => sprintf($this->language_service->txt('log_entry_writing_removed_authorized'), $object, $subject),
            TYPE::CORRECTION_REMOVE_AUTHORIZATION => sprintf($this->language_service->txt('log_entry_removed_authorization'), $object, $subject),
            TYPE::CORRECTION_REMOVE_OWN_AUTHORIZATION => sprintf($this->language_service->txt('log_entry_removed_own_authorization'), $object, $subject),
            TYPE::WRITER_NOTE => sprintf($this->language_service->txt('log_entry_writer_note'), $object, $subject),
            TYPE::NOTE => sprintf($this->language_service->txt('log_entry_note'), $subject)
        };

        $log_entry = $this->repos->logEntry()->new()
                                             ->setTimestamp($timestamp)
                                             ->setAssId($this->ass_id)
                                             ->setCategory($category)
                                             ->setEntry(trim($entry . ' ' .  $note));

        $this->repos->logEntry()->create($log_entry);
    }

    public function createCsv(): string
    {
        return "";
        // TODO: Implement createCsv() method.
    }

    public function all(): array
    {
        return $this->repos->logEntry()->allByAssId($this->ass_id);
    }
}