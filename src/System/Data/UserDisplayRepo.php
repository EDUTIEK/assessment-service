<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use Edutiek\AssessmentService\System\Data\UserDisplay;

interface UserDisplayRepo
{
    /**
     * Get the display properties of a single user
     */
    public function one(int $id, ?string $back_link): UserDisplay;

    /**
     * Get display properties of multiple users
     * @param int[] $ids
     * @return UserDisplay[]
     */
    public function some(array $ids, ?string $back_link): array;

}
