<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface PermissionsRepo
{
    public function one(int $ass_id, int $context_id, int $user_id): Permissions;

    /**
     * @param int $ass_id
     * @param int $user_id
     * @return Permissions[]
     */
    public function allByAssIdAndUserId(int $ass_id, int $user_id): array;
}
