<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Transform;

interface FullService
{
    /**
     * Cleanup HTML code from a richtext editor to be securely displayed
     */
    public function cleanupRichText(?string $text): string;

    /**
     * Trim a rich text to get an empty string if the text has only empty elements
     */
    public function trimRichText(?string $text): ?string;
}
