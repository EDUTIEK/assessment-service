<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDataRepo;
use Edutiek\AssessmentService\System\Data\UserDisplay;
use Edutiek\AssessmentService\System\Data\UserDisplayRepo;

readonly class Service implements ReadService
{
    public function __construct(
        private UserDataRepo $user_repo,
        private UserDisplayRepo $user_display
    ) {
    }

    public function getUser(int $id) : ?UserData
    {
        return $this->user_repo->getOne($id);
    }

    public function getUsersByIds(array $ids): array
    {
        return $this->user_repo->getSome($ids);
    }

    public function getCurrentUser(): ?UserData
    {
        return $this->user_repo->getCurrent();
    }

    public function getUserDisplay(int $id, ?string $back_link): UserDisplay
    {
        return $this->user_display->getOne($id, $back_link);
    }

    public function getUserDisplaysByIds(array $ids, ?string $back_link): array
    {
        return $this->user_display->getSome($ids, $back_link);
    }
}
