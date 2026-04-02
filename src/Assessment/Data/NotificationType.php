<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum NotificationType: string
{
    case CORRECTOR_PROCEDURE_STARTED = 'corrector_procedure_started';
    case CORRECTOR_AUTHORIZATION_REMOVED = 'corrector_authorization_removed';
    case CORRECTOR_WRITING_CHANGED = 'corrector_writing_changed';
    case WRITER_CORRECTION_FINALIZED = 'writer_correction_finalized';
    case ADMIN_STITCH_NEEDED = 'admin_stitch_needed';
    case ADMIN_WRITING_AUTHORIZED = 'admin_writing_authorized';

    public static function availableTypes(): array
    {
        return  [
            self::WRITER_CORRECTION_FINALIZED
        ];
    }

    public function titleLangVar(): string
    {
        return 'notification_title_' . $this->value;
    }

    public function descriptionLangVar(): string
    {
        return 'notification_info_' . $this->value;
    }

    public function subjectLangVar(): string
    {
        return 'notification_subject_' . $this->value;
    }
}
