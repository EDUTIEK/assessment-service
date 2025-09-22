<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImage;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\EssayImage;

interface FullService
{
    /**
     * Get the page images of an essay (create, if needed)
     * @return EssayImage[]
     */
    public function getByEssayId(int $id): array;

    /**
     * Create the page images of an essay (replace existing images)
     * @return EssayImage[]
     */
    public function createByEssayId(Essay $essay): array;


    /**
     * Delete the page images of an essay
     * @return EssayImage[]
     */
    public function deleteByEssayId(int $essay_id): void;
}
