<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use ILIAS\Plugin\LongEssayAssessment\System\Data\UserDisplay;

interface UserRepo
{
    /**
     * Get the data of a user by its id
     */
    public function getUser(int $id): ?UserData;

    /**
     * Get the data of multiple users by their ids
     * @param int[] $ids
     * @return UserData[]
     */
    public function getUsersByIds(array $ids): array;

    /**
     * Get the currently active user
     * This might be null, e.g. in case of cron job
     */
    public function getCurrentUser(): ?UserData;

    /**
     * Get the display properties of a single user
     */
    public function getUserDisplay(int $id, ?string $back_link): UserDisplay;

    /**
     * Get display properties of multiple users
     * @param int[] $ids
     * @return UserDisplay[]
     */
    public function getUserDisplaysByIds(array $ids, ?string $back_link): array;

}
