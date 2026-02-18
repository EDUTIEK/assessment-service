<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\ImageProcessing;

use Edutiek\AssessmentService\Task\CorrectorComment\CorrectorCommentInfo;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;

interface FullService
{
    /**
     * Apply the marks of comments to a page image
     * @param CorrectorCommentInfo[] $infos
     */
    public function applyCommentsMarks(int $page_number, ImageDescriptor $image, array $infos): ImageDescriptor;
}
