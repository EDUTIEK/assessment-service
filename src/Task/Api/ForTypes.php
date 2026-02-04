<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Task\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentReadService;
use Edutiek\AssessmentService\Task\CorrectorComment\ReadService as CorrectorCommentReadService;
use Edutiek\AssessmentService\Task\Manager\ReadService as ManagerReadService;

readonly class ForTypes
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function tasks(): ManagerReadService
    {
        return $this->internal->taskManager($this->ass_id, $this->user_id);
    }

    public function correctorAssignments(): CorrectorAssignmentReadService
    {
        return $this->internal->correctorAssignments($this->ass_id, $this->user_id);
    }

    public function correctorComments(): CorrectorCommentReadService
    {
        return $this->internal->correctorComment($this->ass_id, $this->user_id);
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }
}
