<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDisplay;

interface ReadService
{
    public function getUser(int $id) : ?UserData;

    /**
     * @param int[] $ids
     * @return UserData[]
     */
    public function getUsersByIds(array $ids): array;

    public function getCurrentUser(): ?UserData;

    public function getUserDisplay(int $id, ?string $back_link): UserDisplay;

    public function getUserDisplaysByIds(array $ids, ?string $back_link): array;

    public function getUserIdByLogin(string $login): int;
}
