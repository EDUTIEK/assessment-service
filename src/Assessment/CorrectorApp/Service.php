<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\CorrectorApp;

use Edutiek\AssessmentService\Assessment\Api\ComponentApiFactory;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\OpenService;
use Edutiek\AssessmentService\Assessment\Apps\RestException;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestService;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\Config\FrontendModule;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Disposition;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

readonly class Service implements OpenService, RestService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private ConfigService $config,
        private OpenHelper $open_helper,
        private RestHelper $rest_helper,
        private ComponentApiFactory $apis,
        private App $app,
        private Delivery $delivery,
    ) {
    }

    public function open(string $return_url): never
    {
        $this->open_helper->setCommonFrontendParams($return_url);
        $this->open_helper->openFrontend($this->config->getFrontendUrl(FrontendModule::CORRECTOR));
    }

    public function handle(): never
    {
        $this->app->run();
        exit;
    }
}
