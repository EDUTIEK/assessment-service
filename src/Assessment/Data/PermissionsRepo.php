<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface PermissionsRepo
{
    public function one(int $ass_id, int $context_id, int $user_id): ?Permissions;
}
