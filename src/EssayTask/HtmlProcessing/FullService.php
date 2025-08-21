<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;

interface FullService
{
    /**
     * Fill a template with data
     */
    public function fillTemplate(string $template, array $data): string;

    /**
     * Process the written text for usage in the correction
     * This will add the paragraph numbers and headline prefixes
     * and split up all text to single word embedded in <w-p> elements.
     *      the 'w' attribute is the word number
     *      the 'p' attribute is the paragraph number
     *
     * @param bool $forPdf  styles and tweaks for pdf processing should be added
     */
    public function processWrittenText(?Essay $essay, WritingSettings $settings, bool $forPdf = false) : string;

    /**
     * Process the written text for inclusion in a pdf with comments at the side comments
     * The text must have been processed with processedWrittenText()
     * @param CorrectorComment[]  $comments
     */
    public function processCommentsForPdf(?Essay $essay, WritingSettings $writingSettings, CorrectionSettings $correctionSettings, array $comments) : string;
}