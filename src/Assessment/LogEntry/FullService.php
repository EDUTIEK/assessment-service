<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

interface FullService
{
    public function addEntry(Type $type, ?MentionUser $subject_mention, ?MentionUser $object_mention = null, ?string $note = null) : void;

    /**
     * Create the log as a CSV string
     */
    public function createCsv() : string;
}