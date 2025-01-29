<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

use Edutiek\AssessmentService\System\Data\UserRepo;
use Edutiek\AssessmentService\System\Data\UserData;
use ILIAS\Plugin\LongEssayAssessment\System\Data\UserDisplay;

readonly class Service implements ReadService
{
    public function __construct(
        private UserRepo $user_repo
    ) {
    }

    public function getUser(int $id) : ?UserData
    {
        return $this->user_repo->getUser($id);
    }

    public function getUsersByIds(array $ids): array
    {
        return $this->user_repo->getUsersByIds($ids);
    }

    public function getUserByFormalId(string $formal_id)
    {
        return $this->user_repo->getUserByFormalId($formal_id);
    }

    public function getCurrentUser(): ?UserData
    {
        return $this->user_repo->getCurrentUser();
    }

    public function getUserDisplay(int $id, ?string $back_link): UserDisplay
    {
        return $this->user_repo->getUserDisplay($id, $back_link);
    }

    public function getUserDisplaysByIds(array $ids, ?string $back_link): array
    {
        return $this->user_repo->getUserDisplaysByIds($ids, $back_link);
    }
}
