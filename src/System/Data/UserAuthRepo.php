<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface UserAuthRepo
{
    public function get(int $user_id): UserAuth;
    public function save(UserAuth $entity): void;
    public function delete(int $user_id): void;
}
