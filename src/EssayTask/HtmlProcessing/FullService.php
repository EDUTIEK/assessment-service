<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\Essay;

interface FullService
{
    /**
     * Get the written text for display in the corrector web app
     */
    public function getWrittenTextForCorrection(?Essay $essay): string;

    /**
     * Get the written text for for inclusion in a PDF file
     */
    public function getWrittenTextForPdf(?Essay $essay): string;

    /**
     * Get the marked and commented text for inclusion in a PDF file
     * @param CorrectorComment[]  $comments
     */
    public function getCorrectedTextForPdf(?Essay $essay, array $comments): string;

    /**
     * Get the html formatted comments for side display in a PDF File
     * @param CorrectorComment[] $comments
     */
    public function getCommentsHtml(array $comments): string;
}
