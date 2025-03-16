<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Transform;

class Service implements FullService
{
    public function cleanupRichText(?string $text): string
    {
        // allow only HTML tags that are supported in the writer and corrector app
        return strip_tags(
            (string) $text,
            '<p><div><br><strong><b><em><i><u><ol><ul><li><h1><h2><h3><h4><h5><h6><pre>'
        );
    }

    public function trimRichText(?string $text): ?string
    {
        if (!isset($text)) {
            return null;
        }

        if (empty(trim(strip_tags($text)))) {
            return '';
        }
        return trim($text);
    }
}
