<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeManager as ManagerInterface;
use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
use Edutiek\AssessmentService\System\File\FileUsageFinder;
use Edutiek\AssessmentService\Assessment\PersonalDataExport;

readonly class ForServices implements TypeApi
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function manager(int $ass_id, int $task_id, int $user_id): ManagerInterface
    {
        return $this->internal->manager($ass_id, $task_id, $user_id);
    }

    public function writerBridge(int $ass_id, int $context_id, int $user_id): ?AppBridge
    {
        return $this->internal->writerBridge($ass_id, $user_id);
    }

    public function correctorBridge(int $ass_id, int $context_id, int $user_id): ?AppCorrectorBridge
    {
        return $this->internal->correctorBridge($ass_id, $user_id);
    }

    public function writingPartProvider(int $ass_id, int $context_id, int $user_id): ?PdfPartProvider
    {
        return $this->internal->writingPartProvider($ass_id, $user_id);
    }

    public function correctionPartProvider(int $ass_id, int $context_id, int $user_id): ?PdfPartProvider
    {
        return $this->internal->correctionPartProvider($ass_id, $user_id);
    }

    public function fileUsageFinder(): FileUsageFinder
    {
        return $this->internal->fileUsageFinder();
    }

    public function personalDataCollector(): PersonalDataExport\UsageResolver
    {
        return $this->internal->personalDataCollector();
    }
}
