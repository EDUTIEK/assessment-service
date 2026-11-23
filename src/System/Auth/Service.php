<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Auth;

use Edutiek\AssessmentService\System\Data\UserDataRepo;
use Edutiek\AssessmentService\System\Data\UserAuthRepo;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Mail\Delivery as Mail;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Data\UserAuth;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use PHPUnit\Framework\Exception;

readonly class Service implements FullService
{
    public function __construct(
        private UserDataRepo $user_repo,
        private UserAuthRepo $auth_repo,
        private AuthHandler $auth_handler,
        private Mail $mail,
        private ConfigService $config,
        private Language $lang,
    ) {
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

    public function getAgreement(int $user_id): string
    {
        return $this->auth_handler->getAgreement($user_id);
    }

    public function hasAcceptedAgreement(int $user_id): bool
    {
        return $this->auth_handler->hasAcceptedAgreement($user_id);
    }

    public function acceptAgreement(int $user_id): void
    {
        $this->auth_handler->acceptAgreement($user_id);
    }

    public function requestPasswordChange(string $email, Frontend $frontend): Result
    {
        $ids = $this->user_repo->idsByEmail($email);
        if (empty($ids)) {
            // prevent if an email is registered
            return new Result(true);
        }

        $reset_code = UserAuth::createCode();

        $logins = [];
        foreach ($ids as $user_id) {
            $logins[] = $this->user_repo->loginById($user_id);
            $auth = $this->auth_repo->get($user_id);
            if (!$auth->isNewResetCodePossible()) {
                // prevent checking if an email is registered
                return new Result(true);
            }
            $auth->setNewResetCode($reset_code);
            $this->auth_repo->save($auth);
        }

        $subject = $this->lang->txt('password_change_subject');
        $body = $this->lang->txt('password_change_body');
        $link = $this->config->getFrontendUrl($frontend);

        $body = str_replace('[email]', $email, $body);
        $body = str_replace('[logins]', implode('<br />', $logins), $body);
        $body = str_replace('[code]', $reset_code, $body);
        $body = str_replace('[link]', $link, $body);

        if (!$this->mail->external($subject, $body, [$email])) {
            return new Result(false, $this->lang->txt('password_change_sending_failed'));
        }

        return new Result(true);
    }


    public function removePasswordChange(string $email): void
    {
        $ids = $this->user_repo->idsByEmail($email);
        foreach ($ids as $user_id) {
            $auth = $this->auth_repo->get($user_id);
            $auth->setResetCode(null)->setResetValidUntil(null);
            $this->auth_repo->save($auth);
        }
    }

    public function checkPasswordChangeCode(string $login, string $code): Result
    {
        $user_id = $this->user_repo->idByLogin($login);
        if (!empty($user_id)) {
            $auth = $this->auth_repo->get($user_id);
            if ($auth->isResetCodeValid($code)) {
                return  new Result(true);
            }
        }

        return new Result(false, $this->lang->txt('password_change_code_or_login_wrong'));
    }

    public function changePassword(int $user_id, string $password): Result
    {
        return $this->auth_handler->changePassword($user_id, $password);
    }
}
