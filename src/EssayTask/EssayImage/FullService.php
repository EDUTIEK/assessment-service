<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImage;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\EssayImage;

interface FullService
{
    /**
     * @return EssayImage[]
     */
    public function getByEssayId(int $id): array;
    public function createByEssayId(Essay $essay): int;
    public function deleteByEssayId(int $essay_id): void;
}
