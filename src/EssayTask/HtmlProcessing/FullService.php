<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;

interface FullService
{
    /**
     * Get the written text for display in the corrector web app
     */
    public function getWrittenTextForCorrection(?Essay $essay, WritingSettings $settings): string;

    /**
     * Get the written text for for inclusion in a PDF file
     */
    public function getWrittenTextForPdf(?Essay $essay, WritingSettings $settings): string;

    /**
     * Get the marked and commented text for inclusion in a PDF file
     * @param CorrectorComment[]  $comments
     */
    public function getCorrectedTextForPdf(
        ?Essay $essay,
        WritingSettings $writingSettings,
        CorrectionSettings $correctionSettings,
        array $comments
    ): string;
}
