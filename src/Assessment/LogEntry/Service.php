<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Edutiek\AssessmentService\System\Format\Service as FormatService;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as SpreadsheetService;
use Edutiek\AssessmentService\Assessment\Data\LogEntry;
use Edutiek\AssessmentService\Assessment\Data\Alert;

class Service implements FullService
{
    public function __construct(
        private readonly int $ass_id,
        private Repositories $repos,
        private LanguageService $lang,
        private FormatService $format,
        private UserService $user_service,
        private SpreadsheetService $spreadsheet
    ) {

    }

    private function getNameFromMention(?MentionUser $mention): string
    {
        $user_data = null;

        $id = match($mention?->type) {
            UserType::Writer => $this->repos->corrector()->one($mention->id)?->getUserId(),
            UserType::Corrector => $this->repos->writer()->one($mention->id)?->getUserId(),
            UserType::System => $mention->id,
            null => null
        };
        $user_data = $id !== null ? $this->user_service->getUser($id) : null;

        return $user_data?->getListname(true) ?? $this->lang->txt('unknown');
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

        $entry = match($type) {
            Type::WORKING_TIME_CHANGE => sprintf($this->lang->txt('log_entry_working_time_changed'), $object, $subject),
            Type::WORKING_TIME_DELETE => sprintf(($this->lang->txt('log_entry_working_time_deleted')), $object, $subject),
            TYPE::WRITER_EXCLUSION => sprintf(($this->lang->txt('log_entry_writer_exclusion')), $object, $subject),
            TYPE::WRITER_REPEAL_EXCLUSION => sprintf($this->lang->txt('log_entry_writer_repealed_exclusion'), $object, $subject),
            TYPE::WRITER_REMOVAL => sprintf(($this->lang->txt('log_entry_writer_removal')), $object, $subject),
            TYPE::WRITING_POST_AUTHORIZED => sprintf($this->lang->txt('log_entry_writing_post_authorized'), $object, $subject),
            TYPE::WRITING_REMOVE_AUTHORIZATION => sprintf($this->lang->txt('log_entry_writing_removed_authorized'), $object, $subject),
            TYPE::CORRECTION_REMOVE_AUTHORIZATION => sprintf($this->lang->txt('log_entry_removed_authorization'), $object, $subject),
            TYPE::CORRECTION_REMOVE_OWN_AUTHORIZATION => sprintf($this->lang->txt('log_entry_removed_own_authorization'), $object, $subject),
            TYPE::WRITER_NOTE => sprintf($this->lang->txt('log_entry_writer_note'), $object, $subject),
            TYPE::NOTE => sprintf($this->lang->txt('log_entry_note'), $subject)
        };

        $log_entry = $this->repos->logEntry()->new()
                                             ->setTimestamp($timestamp)
                                             ->setAssId($this->ass_id)
                                             ->setCategory($category)
                                             ->setEntry(trim($entry . ' ' . $note));

        $this->repos->logEntry()->create($log_entry);
    }

    public function all(): array
    {
        return $this->repos->logEntry()->allByAssId($this->ass_id);
    }

    public function export(ExportType $type): string
    {
        $header = [
            'log_time' => $this->lang->txt('log_time'),
            'log_category' => $this->lang->txt('log_category'),
            'log_alert_to' => $this->lang->txt('log_alert_to'),
            'log_content' => $this->lang->txt('log_content'),
        ];

        $entries = [];
        foreach ($this->repos->logEntry()->allByAssId($this->ass_id) as $entry) {
            $entries[$this->format->date($entry->getTimestamp(), true) . ' log' . $entry->getId()] = $entry;
        }
        foreach ($this->repos->alert()->allByAssId($this->ass_id) as $entry) {
            $entries[$this->format->date($entry->getShownFrom(), true) . ' alert' . $entry->getId()] = $entry;
        }
        sort($entries);

        $users = [];
        $rows = [];
        foreach ($entries as $entry) {
            if ($entry instanceof LogEntry) {
                $rows[] = [
                    'log_time' => $this->format->logDate($entry->getTimestamp()),
                    'log_category' => $this->lang->txt('log_cat_' . $entry->getCategory()->value),
                    'log_alert_to' => '',
                    'log_content' => $entry->getEntry(),
                ];
            } elseif ($entry instanceof Alert) {
                if ($entry->getWriterId() !== null) {
                    $user = $users[$entry->getWriterId()] ??= $this->user_service->getUser(
                        $this->repos->writer()->one($entry->getWriterId())?->getUserId() ?? 0
                    );
                    $to = $user?->getFullname(true) ?? '';
                } else {
                    $to = $this->lang->txt('log_alert_to_all');
                }

                $rows[] = [
                    'log_time' => $this->format->logDate($entry->getShownFrom()),
                    'log_category' => $this->lang->txt('log_cat_alert'),
                    'log_alert_to' => $to,
                    'log_content' => $entry->getMessage(),
                ];
            }
        }

        return $this->spreadsheet->dataToFile($header, $rows, $type, $this->lang->txt('log_filename'));
    }
}
