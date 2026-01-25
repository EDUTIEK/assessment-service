<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\WritingSteps;

interface FullService
{
    /**
     * Create a ZIP file with the writing stps of all essays of a writer
     * @param int $writer_id
     * @return string   temporary file storage id
     */
    public function createExport(int $writer_id): string;
}
