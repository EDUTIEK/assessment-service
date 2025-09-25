<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

interface TasksService
{
    public function addEntry(Type $type, ?MentionUser $subject_mention, ?MentionUser $object_mention = null, ?string $note = null): void;
}
