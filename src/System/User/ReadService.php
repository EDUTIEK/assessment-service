<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDisplay;

interface ReadService
{
    public function getUser(int $id): ?UserData;

    /**
     * @param int[] $ids
     * @return array<int, UserData> indexed by user_id
     */
    public function getUsersByIds(array $ids): array;

    public function getCurrentUser(): ?UserData;

    public function getUserDisplay(int $id, ?string $back_link): UserDisplay;

    public function getUserDisplaysByIds(array $ids, ?string $back_link): array;

    /**
     * Get a user id by login
     * A non-existing user returns 0
     */
    public function getUserIdByLogin(string $login): int;

    /**
     * Get the login by user_id
     * A non-existing user returns ''
     */
    public function getLoginByUserId(int $id): string;
}
