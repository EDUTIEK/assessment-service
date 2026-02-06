<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorTemplate;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorTemplate;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Task\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private ChecksService $checks,
        private Repositories $repos
    ) {
    }

    public function getSharableCorrectorIds(int $task_id): array
    {
        return $this->repos->correctorTemplates()->sharableCorrectorIds($task_id);
    }

    public function getByTaskIdAndCorrectorId(int $task_id, int $corrector_id): CorrectorTemplate
    {
        $template = $this->repos->correctorTemplates()->oneByTaskIdAndCorrectorId($task_id, $corrector_id)
            ?? $this->repos->correctorTemplates()->new($task_id, $corrector_id);

        $this->checkScope($template);
        return $template;
    }

    public function save(CorrectorTemplate $template)
    {
        $this->checkScope($template);
        $this->repos->correctorTemplates()->save($template);
    }

    public function checkScope(CorrectorTemplate $template)
    {
        if (!$this->checks->hasTask($template->getTaskId())) {
            throw new ApiException('wrong task_id', ApiException::ID_SCOPE);
        }
        if (!$this->checks->hasCorrector($template->getCorrectorId())) {
            throw new ApiException('wrong corrector_id', ApiException::ID_SCOPE);
        }
    }
}
