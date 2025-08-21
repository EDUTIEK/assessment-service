<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Comments;

use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;

interface FullService
{
    /**
     * Get html formatted comments for side display in the pdf
     * @param CorrectorComment[] $comments
     */
    public function getCommentsHtml(array $comments, CorrectionSettings $settings) : string;

    /**
     * Get the sorted and labelled comments of a parent (page or paragraph)
     * @param CorrectorComment[] $comments
     * @return CorrectorComment[]
     */
    public function getSortedCommentsOfParent(array $comments, int $parent_no) : array;

    /**
     * Apply the marks of comments to a page image
     * @param CorrectorComment[] $comments
     */
    public function applyCommentsMarks(int $page_number, ImageDescriptor $image, array $comments) : ImageDescriptor;

    /**
     * Get the text background color of a list of overlapping comments
     * Cardinal failures and excellent passages should have precedence
     * @param CorrectorComment[] $comments
     */
    public function getTextBackgroundColor(array $comments) : string;
}