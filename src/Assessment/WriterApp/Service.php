<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestService;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\Config\FrontendModule;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class Service implements OpenService, RestService
{
    private Request $request;
    private Response $response;
    private array $args;
    private array $params;
    private TokenPurpose $purpose;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly ConfigService $config,
        private readonly OpenHelper $open_helper,
        private readonly RestHelper $rest_helper,
        private readonly App $app,
        private readonly Repositories $repos,
    ) {
    }

    public function open(string $return_url): never
    {
        $this->open_helper->setCommonFrontendParams($return_url);

        // add the hash of the current essay content
        // this will be used to check if the writer content is up to date
        // todo: either use hashes of all essays or omit
        //$this->open_helper->setFrontendParam('xlasLastHash', (string) $essay->getWrittenHash());

        $this->open_helper->openFrontend($this->config->getFrontendUrl(FrontendModule::WRITER));
    }

    /**
     * Handle a REST call
     */
    public function handle(): never
    {
        $this->app->get('/data', [$this,'getData']);
        $this->app->get('/update', [$this,'getUpdate']);
        $this->app->get('/file/{key}', [$this,'getFile']);
        $this->app->put('/start', [$this,'putStart']);
        $this->app->put('/steps', [$this,'putSteps']);
        $this->app->put('/changes', [$this, 'putChanges']);
        $this->app->put('/final', [$this,'putFinal']);
        $this->app->run();
        exit;
    }

    protected function prepare(Request $request, Response $response, array $args, TokenPurpose $purpose): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $this->params = $request->getQueryParams();
        $this->purpose = $purpose;

        $this->rest_helper->checkAuth($purpose, $this->params['signature'] ?? '');
        $this->rest_helper->checkAccess();
    }

    /**
     * GET the data for initializing the writer
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);
        return $response;
    }

    /**
     * GET the data for updating the writer
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        return $response;
    }

    /**
     * PUT the writing start timestamp
     */
    public function putStart(Request $request, Response $response, array $args): Response
    {
        return $response;
    }

    /**
     * PUT a list of writing steps
     */
    public function putSteps(Request $request, Response $response, array $args): Response
    {
        return $response;
    }

    /**
     * PUT the unsent changes in the writer app
     * Currently only the notes are sent as changes
     *
     * The changes are available from the parsed body as assoc arrays with properties:
     * - key: existing or temporary key of the object to be saved
     *
     * The added or changed data is wrapped as 'payload' in the change
     */
    public function putChanges(Request $request, Response $response, array $args): Response
    {
        return $response;
    }
}
