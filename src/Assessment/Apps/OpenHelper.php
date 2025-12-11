<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as Authentication;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Token;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use JetBrains\PhpStorm\NoReturn;
use SimpleSAML\Utils\Auth;

readonly class OpenHelper
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private int $user_id,
        private Authentication $auth,
        private ConfigReadService $config_service
    ) {
    }

    /**
     * Set the parameters which are common to all frontends
     */
    public function setCommonFrontendParams(string $return_url): void
    {
        $this->setFrontendParam('ReturnUrl', $return_url);
        $this->setFrontendParam('BackendUrl', $this->config_service->getSetup()->getBackendUrl());
        $this->setFrontendParam('AssId', (string) $this->ass_id);
        $this->setFrontendParam('ContextId', (string) $this->context_id);
        $this->setFrontendParam('UserId', (string) $this->user_id);
        $this->setFrontendParam('DataToken', $this->createToken(TokenPurpose::DATA)->getToken());
        $this->setFrontendParam('FileToken', $this->createToken(TokenPurpose::FILE)->getToken());
    }

    /**
     * Set a parameter for the frontend
     *
     * Parameters are sent as cookies over https
     * They are only needed when the frontend is initialized and can expire afterwards (1 minute)
     * They should be set for the whole server path to allow a different frontend location during development
     */
    public function setFrontendParam(string $name, string $value): void
    {
        setcookie(
            'xlas' . $name,
            $value,
            [
                'expires' => time() + 60,
                'path' => '/',
                'domain' => '',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => false,
                'sameSite' => 'Strict' // None, Lax, Strict
            ]
        );
    }

    /**
     * Actually redirect to the frontend
     */
    public function openFrontend(string $frontend_url): never
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Location: ' . $frontend_url);
        exit;
    }

    /**
     * Generate a new data token and save it
     */
    private function createToken(TokenPurpose $purpose): Token
    {
        $token = $this->auth->newToken($this->user_id, $purpose);
        $this->auth->saveToken($token);
        return $token;
    }
}
