<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DeepLink;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Config\Frontend;

class Service implements FullService
{
    public function __construct(
        private Repositories $repos,
        private ConfigService $config,
    ) {
    }

    public function backend(int $ass_id, int $context_id = null, int $user_id = null)
    {
        $context_repo = $this->repos->contextInfo();
        if ($context_id === null && $user_id !== null) {
            $context_id = $this->repos->contextInfo()->readableContextId($ass_id, $user_id);
        }

        return $context_repo->link($ass_id, $context_id);
    }

    public function correction(int $ass_id, int $context_id = null, ?int $user_id = null, ?int $task_id = null, ?int $writer_id = null)
    {
        $context_repo = $this->repos->contextInfo();
        if ($context_id === null && $user_id !== null) {
            $context_id = $this->repos->contextInfo()->readableContextId($ass_id, $user_id);
        }

        if ($this->config->getSetup()->isFrontendWithLogin(Frontend::CORRECTOR)) {
            return $this->correctionApp($ass_id, $context_id, $user_id, $task_id, $writer_id);
        } else {
            return $context_repo->link($ass_id, $context_id);
        }
    }

    public function correctionApp(int $ass_id, int $context_id = null, ?int $user_id = null, ?int $task_id = null, ?int $writer_id = null)
    {
        $context_repo = $this->repos->contextInfo();
        if ($context_id === null && $user_id !== null) {
            $context_id = $this->repos->contextInfo()->readableContextId($ass_id, $user_id);
        }

        $query = null;
        if ($context_id !== null) {
            $query = 'open/' . $ass_id . '/' . $context_id;
        }
        if ($task_id !== null) {
            $query .= '/' . $task_id;
        }
        if ($writer_id !== null) {
            $query .= '/' . $writer_id;
        }
        return $this->config->getFrontendUrl(Frontend::CORRECTOR, $query);

    }
}
