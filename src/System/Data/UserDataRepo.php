<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use Edutiek\AssessmentService\System\Data\UserDisplay;

interface UserDataRepo
{
    /**
     * Get the data of a user by its id
     */
    public function one(int $id): ?UserData;

    /**
     * Get the data of multiple users by their ids
     * @param int[] $ids
     * @return UserData[], indexed by user_id
     */
    public function some(array $ids): array;

    /**
     * Get the currently active user
     * This might be null, e.g. in case of cron job
     */
    public function current(): ?UserData;

    /**
     * Get a user id by login
     * A non-existing user returns 0
     */
    public function idByLogin(string $login): int;

    /**
     * Get the login by user_id
     * A non-existing user returns '
     */
    public function loginById(int $id): string;
}
