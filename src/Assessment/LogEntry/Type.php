<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

enum Type : string
{
    case  NOTE = 'note';
    case  WORKING_TIME_CHANGE = 'working_time_change';
    case  WORKING_TIME_DELETE = 'working_time_delete';
    case  WRITER_NOTE = 'writer_note';
    case  WRITER_REMOVAL = 'writer_removal';
    case  WRITER_EXCLUSION = 'writer_exclusion';
    case  WRITER_REPEAL_EXCLUSION = 'writer_repeal_exclusion';
    case  WRITING_POST_AUTHORIZED = 'writing_post_authorized';
    case  WRITING_REMOVE_AUTHORIZATION = 'writing_remove_authorization';
    case  CORRECTION_REMOVE_AUTHORIZATION = 'correction_remove_authorization';
    case  CORRECTION_REMOVE_OWN_AUTHORIZATION = 'correction_remove_own_authorization';

    public function getCategory() : Category
    {
        return match ($this) {
            self::WRITING_POST_AUTHORIZED, self::WRITING_REMOVE_AUTHORIZATION, self::CORRECTION_REMOVE_AUTHORIZATION,
            self::CORRECTION_REMOVE_OWN_AUTHORIZATION => Category::AUTHORIZE,
            self::NOTE, self::WRITER_NOTE => Category::NOTE,
            self::WORKING_TIME_CHANGE, self::WORKING_TIME_DELETE => Category::WORKING_TIME,
            self::WRITER_REMOVAL, self::WRITER_EXCLUSION, self::WRITER_REPEAL_EXCLUSION => Category::EXCLUSION,
        };
    }
}
