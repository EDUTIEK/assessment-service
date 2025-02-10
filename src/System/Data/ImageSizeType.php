<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

enum ImageSizeType: string
{
    case THUMBNAIL = 'thumbnail';
    case NORMAL = 'normal';
}
