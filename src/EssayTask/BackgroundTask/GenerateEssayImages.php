<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\ComponentJob;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImage;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;
use Exception;

readonly class GenerateEssayImages implements ComponentJob
{
    public function __construct(
        private EssayRepo $essay,
        private EssayImage $essay_image,
    ) {
    }

    public static function withDownload(): bool
    {
        return false;
    }

    public static function allowDelete(): bool
    {
        return false;
    }

    public function run($args): ?string
    {
        $essay_id = (int) ($args[0] ?? 0);
        $essay = $this->essay->one($essay_id);

        if ($essay === null) {
            throw new Exception(sprintf('Essay for essay id %s not found!', $essay_id));
        }
        $this->essay_image->createForEssay($essay);

        return null;
    }
}
