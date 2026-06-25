<?php

namespace Edutiek\AssessmentService\Task\ConstraintHandling;

use Edutiek\AssessmentService\Task\Api\Internal;
use Edutiek\AssessmentService\System\ConstraintHandling\AbstractProvider;
use Edutiek\AssessmentService\Task\Data\Repositories;

class Provider extends AbstractProvider
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal,
        private readonly Repositories $repos,
    ) {
        $this->registerConstraint(CanRemoveWritingAuthorization::class, fn() => new CanRemoveWritingAuthorization(
            $this->repos,
            $this->internal->language($user_id),
        ));
    }
}
