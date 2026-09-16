<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum NotificationType: string
{
    case CORRECTOR_APPROXIMATION_STARTED = 'corrector_approximation_started';
    case CORRECTOR_CONSULTING_STARTED = 'corrector_consulting_started';
    case CORRECTOR_AUTHORIZATION_REMOVED = 'corrector_authorization_removed';
    case CORRECTOR_FIRST_AUTHORIZATION_REMOVED = 'corrector_first_authorization_removed';
    case CORRECTOR_WRITING_CHANGED = 'corrector_writing_changed';
    case WRITER_CORRECTION_FINALIZED = 'writer_correction_finalized';
    case ADMIN_STITCH_NEEDED = 'admin_stitch_needed';
    case CORRECTOR_STITCH_NEEDED = 'corrector_stitch_needed';
    case ADMIN_WRITING_AUTHORIZED = 'admin_writing_authorized';

    public static function allTypes(): array
    {
        // this order is used for the table presentation
        return  [
            self::ADMIN_WRITING_AUTHORIZED,
            self::CORRECTOR_WRITING_CHANGED,
            self::CORRECTOR_FIRST_AUTHORIZATION_REMOVED,
            self::CORRECTOR_AUTHORIZATION_REMOVED,
            self::CORRECTOR_APPROXIMATION_STARTED,
            self::CORRECTOR_CONSULTING_STARTED,
            self::ADMIN_STITCH_NEEDED,
            self::CORRECTOR_STITCH_NEEDED,
            self::WRITER_CORRECTION_FINALIZED,
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
            self::CORRECTOR_APPROXIMATION_STARTED => true,
            self::CORRECTOR_CONSULTING_STARTED => true,
            self::CORRECTOR_AUTHORIZATION_REMOVED => true,
            self::CORRECTOR_FIRST_AUTHORIZATION_REMOVED => true,
            self::CORRECTOR_WRITING_CHANGED => true,
            self::CORRECTOR_STITCH_NEEDED => true,
            self::WRITER_CORRECTION_FINALIZED => false,
            self::ADMIN_STITCH_NEEDED => false,
            self::ADMIN_WRITING_AUTHORIZED => false,
            default => false,
        };
    }

    public function titleLangVar(): string
    {
        return 'notification_' . $this->value . '_title';
    }

    public function descriptionLangVar(): string
    {
        return 'notification_' . $this->value . '_info';
    }

    public function subjectLangVar(): string
    {
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
            case self::CORRECTOR_APPROXIMATION_STARTED:
            case self::CORRECTOR_CONSULTING_STARTED:
            case self::CORRECTOR_AUTHORIZATION_REMOVED:
            case self::CORRECTOR_FIRST_AUTHORIZATION_REMOVED:
            case self::CORRECTOR_WRITING_CHANGED:
            case self::CORRECTOR_STITCH_NEEDED:
                unset($placeholders['writer_name']);
                unset($placeholders['writer_login']);
        }

        if ($this === self::CORRECTOR_AUTHORIZATION_REMOVED) {
            $placeholders['reason'] = 'notification_var_reason';
        }

        return $placeholders;
    }
}
