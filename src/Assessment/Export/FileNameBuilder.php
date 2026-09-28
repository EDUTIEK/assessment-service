<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\ExportType;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskReader;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;

class FileNameBuilder
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private TaskReader $tasks,
        private Language $lang,
    ) {
    }

    /**
     * @param string $extension with dot
     */
    public function build(ExportType $type, string $extension, ?int $task_id = null, ?int $writer_id = null): string
    {
        // assessment
        $filename = $this->repos->properties()->one($this->ass_id)?->getTitle() ?? $this->lang->txt('task');

        // task
        if ($task_id !== null && $this->repos->orgaSettings()->one($this->ass_id)?->getMultiTasks()) {
            $task = $this->tasks->one($task_id);
            if ($task) {
                $filename .= ' - ' . $task->getTitle();
            }
        }

        // writer
        if ($writer_id !== null) {
            $writer = $this->repos->writer()->one($writer_id);
            if ($writer) {
                $filename .= ' - ' . $writer->getPseudonym();
            }
        }

        // type and extension
        $filename .= ' - ' . $this->lang->txt($type->langVar()) . $extension;

        return $this->sanitize($filename);
    }


    public function sanitize(string $filename): string
    {
        // 1. Forbidden characters on windows: \ / : * ? " < > |
        // 2. Control characters (ASCII 0-31 und 127) via \p{Cc}
        // The Modifier 'u' at then end ensures a correct UTF-8 processing
        $pattern = '/[\\\\\/:\*\?"<>\|]|\p{Cc}/u';

        // Replace forbidden characters by spaces
        $sanitized = preg_replace($pattern, ' ', $filename);

        // Reduce subsequent spaces
        $sanitized = preg_replace('/\s+/u', ' ', $sanitized);

        // Remove spaces from start and end
        return trim($sanitized);
    }
}
