<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum ReviewMode: string
{
    case VIEWER = 'viewer';
    case DOWNLOAD = 'download';
    case BOTH = 'both';

    public function langVar()
    {
        return 'review_mode_' . $this->value;
    }

}
