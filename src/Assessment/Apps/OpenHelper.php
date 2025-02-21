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
        private Repositories $repos,
        private ConfigReadService $config_service
    ) {
    }

    /**
     * Set the parameters which are common to all frontends
     */
    public function setCommonFrontendParams(string $return_url): void
    {
        $this->setFrontendParam('Return', $return_url);
        $this->setFrontendParam('Backend', $this->config_service->getSetup()->getBackendUrl());
        $this->setFrontendParam('Assessment', (string) $this->ass_id);
        $this->setFrontendParam('Context', (string) $this->context_id);
        $this->setFrontendParam('User', (string) $this->user_id);
        $this->setFrontendParam('Token', $this->createDataToken()->getToken());
    }

    /**
     * Set a parameter for the frontend
     *
     * Parameters are sent as cookies over https
     * They are only needed when the frontend is initialized and can expire afterwards (1 minute)
     * They should be set for the whole server path to allow a different frontend locations during development
     */
    public function setFrontendParam(string $name, string $value): void
    {
        setcookie(
            'LongEssay' . $name,
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
        // use this if browsers prevent cookies being saved for a redirection
        // $this->redirectByHtml($frontend_url);

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Location: ' . $frontend_url);
        exit;
    }

    /**
     * Generate a new data token and save it
     */
    private function createDataToken(): Token
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::DATA);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::DATA));
        $this->auth->saveToken($token);
        return $token;
    }

    /**
     * Deliver a redirecting HTML page
     * use this if browsers prevent cookies being saved for a redirection
     * @param string $url
     */
    private function redirectByHtml($url): never
    {
        echo '<!DOCTYPE html>
            <html>
            <head>
               <meta http-equiv="refresh" content="0; url=$url">
            </head>
            <body>
               <a href="$url">Redirect to $url ...</a>
            </body>
            </html>';
        exit;
    }
}
