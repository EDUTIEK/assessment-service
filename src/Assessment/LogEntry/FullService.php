<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

interface FullService
{
    public function addEntry(Type $type, MentionUser|int|null $subject_user_id, MentionUser|int|null $object_user_id, ?string $note = null) : void;

    /**
     * Create the log as a CSV string
     */
    public function createCsv() : string;
}