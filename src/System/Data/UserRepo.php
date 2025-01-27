<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface UserRepo
{
    /**
     * Get the data of a user by its id
     */
    public function getUser(int $id): ?UserData;

    /**
     * Get the data of users by their ids
     * @param int[] $ids
     * @return UserData[]
     */
    public function getUsersByIds(array $ids): array;

    /**
     * Get the currently active user
     * This might be null, e.g. in case of cron job
     */
    public function getCurrentUser(): ?UserData;

}
