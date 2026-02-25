<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\Task\CorrectorComment\CorrectorCommentInfo;

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
     * @param CorrectorCommentInfo[]  $infos
     */
    public function getCorrectedTextForPdf(?Essay $essay, array $infos): string;

    /**
     * Get the HTML formatted comments for side display in a PDF File
     * Note: this must be XML compatible because it is used in XSL processing
     * @param CorrectorCommentInfo[] $infos
     */
    public function getCommentsHtml(array $infos): string;
}
