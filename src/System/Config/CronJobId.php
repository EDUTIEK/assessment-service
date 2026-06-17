<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

enum CronJobId: string
{
    case REVIEW_NOTIFICATION = 'xlas_review_notification';
    case FILE_CLEANUP = 'xlas_file_cleanup';

    public static function all(): array
    {
        return [self::REVIEW_NOTIFICATION, self::FILE_CLEANUP];
    }
}
