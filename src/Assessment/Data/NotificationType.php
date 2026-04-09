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
            self::WRITER_CORRECTION_FINALIZED,
            self::CORRECTOR_PROCEDURE_STARTED,
            self::CORRECTOR_AUTHORIZATION_REMOVED,
            self::ADMIN_WRITING_AUTHORIZED,
            self::ADMIN_STITCH_NEEDED,
        ];
    }

    public function hasConfiguredUsers(): bool
    {
        return in_array($this, [
           self::ADMIN_STITCH_NEEDED,
           self::ADMIN_WRITING_AUTHORIZED,
        ]);
    }

    public function defaultActive(): bool
    {
        return match($this) {
            self::CORRECTOR_PROCEDURE_STARTED => true,
            self::CORRECTOR_AUTHORIZATION_REMOVED => true,
            self::CORRECTOR_WRITING_CHANGED => true,
            self::WRITER_CORRECTION_FINALIZED => false,
            self::ADMIN_STITCH_NEEDED => false,
            self::ADMIN_WRITING_AUTHORIZED => false,
            default => false,
        };
    }

    public function titleLangVar(?CorrectionProcedure $procedure = null): string
    {
        if ($this === self::CORRECTOR_PROCEDURE_STARTED) {
            return match($procedure) {
                CorrectionProcedure::APPROXIMATION => 'notification_corrector_approximation_started_title',
                CorrectionProcedure::CONSULTING => 'notification_corrector_consulting_started_title',
                default => 'notification_' . $this->value . '_title',
            };
        }
        return 'notification_' . $this->value . '_title';
    }

    public function descriptionLangVar(?CorrectionProcedure $procedure = null): string
    {
        if ($this === self::CORRECTOR_PROCEDURE_STARTED) {
            return match($procedure) {
                CorrectionProcedure::APPROXIMATION => 'notification_corrector_approximation_started_info',
                CorrectionProcedure::CONSULTING => 'notification_corrector_consulting_started_info',
                default => 'notification_' . $this->value . '_info',
            };
        }
        return 'notification_' . $this->value . '_info';
    }

    public function subjectLangVar(?CorrectionProcedure $procedure = null): string
    {
        if ($this === self::CORRECTOR_PROCEDURE_STARTED) {
            return match($procedure) {
                CorrectionProcedure::APPROXIMATION => 'notification_corrector_approximation_started_subject',
                CorrectionProcedure::CONSULTING => 'notification_corrector_consulting_started_subject',
                default => 'notification_' . $this->value . '_subject',
            };
        }
        return 'notification_' . $this->value . '_subject';
    }

    public function placeholders(): array
    {
        $placeholders = [
            'title' => 'notification_var_title',
            'firstname' => 'notification_var_firstname',
            'lastname' => 'notification_var_lastname',
            'fullname' => 'notification_var_fullname',
            'assessment_title' => 'notification_var_assessment_title',
            'assessment_link' => 'notification_var_assessment_link',
            'writer_login' => 'notification_var_writer_login',
            'writer_name' => 'notification_var_writer_name',
            'writer_pseudonym' => 'notification_var_pseudonym',
        ];

        switch ($this) {
            case self::WRITER_CORRECTION_FINALIZED:
                unset($placeholders['writer_pseudonym']);
                // no break;
            case self::CORRECTOR_PROCEDURE_STARTED:
            case self::CORRECTOR_AUTHORIZATION_REMOVED:
            case self::CORRECTOR_WRITING_CHANGED:
                unset($placeholders['writer_name']);
                unset($placeholders['writer_login']);
        }

        return $placeholders;
    }
}
