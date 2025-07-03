<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\CorrectorApp\Service as CorrectorAppService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\WriterApp\Service as WriterAppService;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Slim\App;
use Slim\Factory\AppFactory;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    /**
     * Internal authentication service for REST handlers
     * (no caching needed, created once per request)
     */
    public function authentication(int $ass_id, int $context_id): AuthenticationService
    {
        return new AuthenticationService(
            $ass_id,
            $context_id,
            $this->dependencies->repositories()
        );
    }

    /**
     * Service for querying permissions
     * (no caching needed, created once per request)
     */
    public function permissions(int $ass_id, int $context_id, int $user_id): PermissionsService
    {
        return new PermissionsService(
            $ass_id,
            $context_id,
            $user_id,
            $this->dependencies->repositories()
        );
    }

    /**
     * REST handler for writer web app
     * (no caching needed, created once per request)
     */
    public function writerApp(int $ass_id, int $context_id, int $user_id): WriterAppService
    {
        return new WriterAppService(
            $ass_id,
            $context_id,
            $user_id,
            $this->openHelper($ass_id, $context_id, $user_id),
            $this->restHelper($ass_id, $context_id, $user_id),
            $this->slimApp(),
            $this->dependencies->repositories()
        );
    }

    /**
     * REST handler for corrector web app
     * (no caching needed, created once per request)
     */
    public function correctorApp(int $ass_id, int $context_id, int $user_id): CorrectorAppService
    {
        return new CorrectorAppService(
            $ass_id,
            $context_id,
            $user_id,
            $this->openHelper($ass_id, $context_id, $user_id),
            $this->restHelper($ass_id, $context_id, $user_id),
            $this->slimApp(),
            $this->dependencies->repositories()
        );
    }

    /**
     * Translation of language variables
     */
    public function language(int $user_id): LanguageService
    {
        $default_code = $this->dependencies->systemApi()->config()->getSetup()->getDefaultLanguage();
        $user_code = $this->dependencies->systemApi()->user()->getUser($user_id)?->getLanguage() ?? $default_code;

        $service = $this->instances[LanguageService::class][$user_id] = $this->dependencies->systemApi()->language()
            ->setDefaultLanguage($user_code)
            ->setLanguage($user_code);

        foreach (array_unique([$default_code, $user_code]) as $code) {
            if (file_exists(__DIR__ . '/../Languages/' . $code . '.php')) {
                $service->addLanguage($code, require(__DIR__ . '/../Languages/' . $code . '.php'));
            }
        }

        return $service;
    }

    /**
     * Helper functions to open the WebApps
     * (no caching needed, created once per request)
     */
    public function openHelper(int $ass_id, int $context_id, int $user_id): OpenHelper
    {
        return new openHelper(
            $ass_id,
            $context_id,
            $user_id,
            $this->authentication($ass_id, $context_id),
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->config()
        );
    }

    /**
     * Helper functions for the REST services
     * (no caching needed, created once per request)
     */
    public function restHelper(int $ass_id, int $context_id, int $user_id): RestHelper
    {
        return new RestHelper(
            $ass_id,
            $context_id,
            $user_id,
            $this->authentication($ass_id, $context_id),
            $this->permissions($ass_id, $context_id, $user_id),
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->config(),
            $this->dependencies->systemApi()->user(),
            $this->dependencies->systemApi()->fileDelivery()
        );
    }

    /**
     * Configured slim app instance
     * (no caching needed, created once per request)
     */
    public function slimApp(): App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);
        return $app;
    }
}
