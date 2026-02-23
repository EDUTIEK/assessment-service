<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\BackgroundTask;

interface FullService
{
    /**
     * Generate the page images of an essay
     */
    public function generateEssayImages(int $essay_id): void;
}
