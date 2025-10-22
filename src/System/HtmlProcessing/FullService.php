<?php

namespace Edutiek\AssessmentService\System\HtmlProcessing;

interface FullService
{
    public function processHtmlForMarking(string $html) : string;
}