<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\ConstraintHandling;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\System\ConstraintHandling\AbstractProvider;

class Provider extends AbstractProvider
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal
    ) {
        $this->registerConstraint(CanChangeWritingContent::class, fn() => new CanChangeWritingContent(
            $this->internal->writer($ass_id, $user_id),
            $this->internal->language($user_id),
        ));
    }
}
