<?php

namespace Edutiek\AssessmentService\System\HtmlProcessing;

/**
 * Version of the Htmp processing
 * This version is stored in an essay to control the processing
 */
enum ServiceVersion: int
{
    /**
     * Change with every added version
     */
    public const CURRENT = 20240052;

    /**
     * First version of long-essay-assessment-service (initial commit date)
     */
    case V_2021_09_23 = 20210923;

    /**
     * Paragraph numbers should be included to the word counter for comment markup
     * Headline prefixes should be included to the word counter for content markup
     */
    case V_2023_12_18 = 20231218;

    /**
     * Empty body elements should be kept by cleanup
     */
    case V_2024_06_03 = 20240603;

    /**
     * First version of assessment-service (initial commit date)
     */
    case V_2024_12_13 = 20240052;
}
