<?php

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

interface PdfPartProvider
{
    /**
     * Get the parts provided for the pdf creation
     * @return PdfConfigPart[]
     */
    public function getAvailableParts(): array;

    /**
     * @return string id of a temporarily saved pdf file
     */
    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        bool $with_header,
        bool $with_footer
    ): ?string;
}
