<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use Throwable;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Rest\RestHandler;
use Edutiek\AssessmentService\System\Rest\RestException;
use Edutiek\AssessmentService\System\Rest\RestHelper;
use Edutiek\AssessmentService\Assessment\Api\ApiException;

class Service implements OpenService, RestHandler
{
    private Frontend $frontend;
    private int $ass_id;
    private int $context_id;
    private int $user_id;

    public function __construct(
        private readonly ConfigService $config,
        private readonly RestHelper $rest,
        private readonly Internal $internal
    ) {
    }

    /**
     * Init the service properties for a use by the client api
     * the API already provides the assessment id and user id
     */
    public function initForClientApi(int $ass_id, int $user_id): self
    {
        $this->ass_id = $ass_id;
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * Open the writer frontend
     */
    public function openWriter(int $context_id, string $return_url): never
    {
        $this->frontend = Frontend::WRITER;
        $this->context_id = $context_id;

        $token = $this->rest->createToken($this->user_id);
        $this->setCommonFrontendParams($return_url, $token->getToken());

        // must be done after the token is created
        $settings = $this->internal->orgaSettings($this->ass_id, $this->user_id)->get();
        if ($settings?->getDashboard()) {
            $writer = $this->internal->writer($this->ass_id, $this->user_id)->oneByUserId($this->user_id);
            if ($writer !== null) {
                $this->internal->writerClient($this->ass_id, $this->user_id)->create($writer, $token);
            }
        }

        $this->openFrontend($this->config->getFrontendUrl($this->frontend));
    }

    /**
     * Open the corrector frontend
     */
    public function openCorrector(int $context_id, string $return_url, ?int $task_id, ?int $writer_id, ?bool $as_admin = false): void
    {
        $this->frontend = Frontend::CORRECTOR;
        $this->context_id = $context_id;
        $permissions = $this->internal->permissions($this->ass_id, $this->context_id, $this->user_id);
        $this->setCommonFrontendParams($return_url, $this->rest->createToken($this->user_id)->getToken());
        $this->setFrontendParam('TaskId', (string) $task_id);
        $this->setFrontendParam('WriterId', (string) $writer_id);
        $this->setFrontendParam('AsAdmin', ($as_admin && $permissions->canMaintainCorrectors()) ? 'true' : 'false');

        $this->openFrontend($this->config->getFrontendUrl($this->frontend));
    }

    /**
     * Handle a REST call
     */
    public function handle(): never
    {
        try {
            $this->rest->init();

            $parts = explode('/', $this->rest->getRoute());
            $this->frontend = Frontend::fromRoutePart($parts[1]);
            if ($this->frontend === null) {
                throw new RestException("Frontend not found by the REST route", RestException::NOT_FOUND);
            }

            // Request is for the AppStart handler (no params required)
            if (($parts[2] ?? '') == 'start') {
                $this->getAppStart()->handle();
            }

            // Other requests need authentication and assessment params
            $this->user_id = $this->rest->authenticate();

            $params = $this->rest->getParams();
            foreach (['ass_id', 'context_id'] as $key) {
                if (!isset($params[$key])) {
                    throw new RestException("Query parameter $key is missing", RestException::NOT_FOUND);
                }
            }
            $this->ass_id = (int) $params['ass_id'];
            $this->context_id = (int) $params['context_id'];

            $this->getApp()->handle();

        } catch (RestException $e) {
            $this->rest->sendPlainResponse($e->getCode(), $e->getMessage());
        } catch (Throwable $e) {
            $this->rest->sendPlainResponse(
                RestException::INTERNAL_SERVER_ERROR,
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
        }
        exit;
    }

    /**
     * Get the app start handler (handling login, logout, assessments)
     */
    private function getAppStart(): AppStart
    {
        if ($this->frontend === Frontend::CORRECTOR) {
            return $this->internal->appStart($this->frontend);
        }
        $this->rest->sendPlainResponse(RestException::NOT_IMPLEMENTED, "Handler for frontend '{$this->frontend->value}' not implemented.");
    }

    /**
     * Get the app handler
     */
    private function getApp(): BaseApp
    {
        switch ($this->frontend) {
            case Frontend::WRITER:
                return $this->internal->appWriter($this->ass_id, $this->context_id, $this->user_id);
            case Frontend::CORRECTOR:
                return $this->internal->appCorrector($this->ass_id, $this->context_id, $this->user_id);
        };
        $this->rest->sendPlainResponse(RestException::NOT_IMPLEMENTED, "Handler for frontend '{$this->frontend->value}' not implemented.");
    }

    /**
     * Set the parameters which are common to all frontends
     */
    public function setCommonFrontendParams(string $return_url, string $token): void
    {
        $this->setFrontendParam('ReturnUrl', $return_url);
        $this->setFrontendParam('UserToken', $token);
        $this->setFrontendParam('BackendUrl', $this->config->getSetup()->getBackendUrl());
        $this->setFrontendParam('AssId', (string) $this->ass_id);
        $this->setFrontendParam('ContextId', (string) $this->context_id);
        $this->setFrontendParam('UserId', (string) $this->user_id);
    }

    /**
     * Set a parameter for the frontend
     *
     * Parameters are sent as cookies over https
     * They are only needed when the frontend is initialized and can expire afterwards (1 minute)
     * They should be set for the whole server path to allow a different frontend location during development
     */
    private function setFrontendParam(string $name, string $value): void
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
    private function openFrontend(string $frontend_url): never
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Location: ' . $frontend_url);
        exit;
    }
}
