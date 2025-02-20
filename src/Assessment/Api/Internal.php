<?php

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\RestHandler\RestHelper;
use Edutiek\AssessmentService\Assessment\RestHandler\Writer;
use Edutiek\AssessmentService\Assessment\RestHandler\Corrector;
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
    public function authentication(int $ass_id, int $context_id): AuthenticationFullService
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
    public function permissions(int $ass_id, int $context_id, int $user_id): PermissionsReadService
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
    public function writer(int $ass_id, int $context_id, int $user_id): Writer
    {
        return new Writer(
            $ass_id,
            $context_id,
            $user_id,
            $this->slimApp(),
            $this->restHelper($ass_id, $context_id, $user_id),
            $this->dependencies->repositories()
        );
    }

    /**
     * REST handler for corrector web app
     * (no caching needed, created once per request)
     */
    public function corrector(int $ass_id, int $context_id, int $user_id): Corrector
    {
        return new Corrector(
            $ass_id,
            $context_id,
            $user_id,
            $this->slimApp(),
            $this->restHelper($ass_id, $context_id, $user_id),
            $this->dependencies->repositories()
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
            $this->dependencies->repositories()
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
