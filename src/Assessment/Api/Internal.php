<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\FullService as AssessmentGradingFullService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\Service as AssessmentGradingService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as CorrectionProcessFullService;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\Service as CorrectionProcessService;
use Edutiek\AssessmentService\Assessment\CorrectorApp\Service as CorrectorAppService;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\Pseudonym\FullService as PseudonymFullService;
use Edutiek\AssessmentService\Assessment\Pseudonym\Service as PseudonymService;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\Assessment\Writer\Service as WriterService;
use Edutiek\AssessmentService\Assessment\WriterApp\WriterBridge;
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

    public function logEntry(int $ass_id): LogEntryService
    {
        return $this->instances[LogEntryService::class][$ass_id] ??= new LogEntryService(
            $ass_id,
            $this->dependencies->repositories(),
            // set user_id 0 to use the system default language
            $this->language(0),
            $this->dependencies->systemApi()->user()
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
            $this->dependencies->repositories(),
            $this->workingTimeFactory($user_id)
        );
    }

    /**
     * Service for creating pseudonyms
     */
    public function pseudonym(): PseudonymFullService
    {
        return $this->instances[PseudonymService::class] ??= new PseudonymService(
            // set user_id 0 to use the system default language
            $this->language(0),
            $this->dependencies->systemApi()->user()
        );
    }

    public function writer(int $ass_id, int $user_id): WriterService
    {
        return $this->instances[WriterService::class][$ass_id] ??= new WriterService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->workingTimeFactory($user_id),
            $this->logEntry($ass_id),
            $this->pseudonym()
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
            $user_id,
            $this->dependencies->systemApi()->config(),
            $this->openHelper($ass_id, $context_id, $user_id),
            $this->restHelper($ass_id, $context_id, $user_id),
            $this->dependencies->taskApi()->taskManager($ass_id, $user_id),
            $this->slimApp(),
            $this->writerBridge($ass_id, $context_id, $user_id),
            $this->dependencies->taskApi()->writerBridge($ass_id, $user_id),
            $this->dependencies->typeApis(),
            $this->dependencies->systemApi()->fileDelivery()
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
        return $this->instances[LanguageService::class][$user_id] ??=
            $this->dependencies->systemApi()->loadLanguagFromFile($user_id, __DIR__ . '/../Languages/');
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
            $this->dependencies->systemApi()->fileDelivery(),
            $this->dependencies->restContext()
        );
    }

    /**
     * Configured slim app instance
     * (no caching needed, created once per request)
     */
    private function slimApp(): App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);
        $app->setBasePath(dirname(parse_url(
            $this->dependencies->systemApi()->config()->getSetup()->getBackendUrl(),
            PHP_URL_PATH
        )));
        return $app;
    }

    private function writerBridge(int $ass_id, $context_id, int $user_id): WriterBridgeInterface
    {
        return $this->instances[WriterBridge::class][$ass_id][$context_id][$user_id] ??= new WriterBridge(
            $ass_id,
            $user_id,
            $this->workingTimeFactory($user_id),
            $this->writer($ass_id, $user_id),
            $this->dependencies->systemApi()->config(),
            $this->dependencies->systemApi()->entity(),
            $this->dependencies->repositories(),
        );
    }

    /**
     * Factory for working time calculations
     */
    public function workingTimeFactory(int $user_id): WorkingTimeFactory
    {
        return $this->instances[WorkingTimeFactory::class][$user_id] ??= new WorkingTimeFactory(
            $this->language($user_id),
        );
    }

    public function assessment_grading(int $ass_id): AssessmentGradingFullService
    {
        return $this->instances[AssessmentGradingService::class][$ass_id] ??= new AssessmentGradingService($ass_id, $this->dependencies->repositories());
    }

    public function correction_process(int $ass_id): CorrectionProcessFullService
    {
        return $this->instances[CorrectionProcessService::class][$ass_id] ??= new CorrectionProcessService($ass_id, $this->dependencies->repositories());
    }
}
