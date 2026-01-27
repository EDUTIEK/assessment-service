<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImage;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;
use Exception;

readonly class GenerateEssayImages implements Job
{
    public function __construct(
        private EssayRepo $essay,
        private EssayImage $essay_image,
    ) {
    }

    public function run(int $essay_id): void
    {
        $essay = $this->essay->one($essay_id);

        if ($essay === null) {
            throw new Exception(sprintf('Essay for essay id %s not found!', $essay_id));
        }
        $this->essay_image->createForEssay($essay);
    }
}
