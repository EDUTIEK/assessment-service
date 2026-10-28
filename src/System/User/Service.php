<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDataRepo;
use Edutiek\AssessmentService\System\Data\UserDisplay;
use Edutiek\AssessmentService\System\Data\UserDisplayRepo;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements ReadService, AuthService
{
    public function __construct(
        private UserDataRepo $user_repo,
        private UserDisplayRepo $user_display,
        private AuthHandler $auth_handler,
        private Language $lang
    ) {
    }

    public function getUser(int $id): ?UserData
    {
        return $this->user_repo->one($id);
    }

    public function getUsersByIds(array $ids): array
    {
        return $this->user_repo->some($ids);
    }

    public function getUserDisplay(int $id, ?string $back_link): UserDisplay
    {
        return $this->user_display->one($id, $back_link);
    }

    public function getUserDisplaysByIds(array $ids, ?string $back_link): array
    {
        return $this->user_display->some($ids, $back_link);
    }

    public function getUserIdByLogin(string $login): int
    {
        return $this->user_repo->idByLogin($login);
    }

    public function getLoginByUserId(int $id): string
    {
        return $this->user_repo->loginById($id);
    }

    public function authenticate(string $login, string $password, ?string $factor): ?int
    {
        $user_id = $this->user_repo->idByLogin($login);
        if (empty($user_id) || !$this->auth_handler->isActive($user_id)) {
            return null;
        }

        if (!$this->auth_handler->checkPassword($user_id, $password)) {
            $this->auth_handler->handleFailedLogin($user_id);
            return null;
        }

        return $user_id;
    }

    public function failedMessage(string $login): string
    {
        $simple = $this->lang->txt('login_failed_short');
        $long = $this->lang->txt('login_failed_long');

        $user_id = $this->user_repo->idByLogin($login);
        if (empty($user_id) || !$this->auth_handler->isPasswordEnabled($user_id)) {
            return $simple;
        }

        if (!$this->isActive($user_id)) {
            return $long;
        }

        return $simple;
    }

    public function isActive(int $user_id): bool
    {
        return $this->auth_handler->isActive($user_id);
    }

    public function getAgreement($user_id): string
    {
        return $this->auth_handler->getAgreement($user_id);
    }

    public function hasAcceptedAgreement($user_id): bool
    {
        return $this->auth_handler->hasAcceptedAgreement($user_id);
    }

    public function acceptAgreement($user_id): void
    {
        $this->auth_handler->acceptAgreement($user_id);
    }
}
