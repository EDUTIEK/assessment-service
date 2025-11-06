<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

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
        $this->open_helper->openFrontend($this->config->getFrontendUrl(FrontendModule::WRITER));
    }

    public function handle(): never
    {
        $this->app->get('/writer/data', [$this,'getData']);
        $this->app->get('/writer/update', [$this,'getUpdate']);
        $this->app->get('/writer/file/{component}/{entity}/{id}', [$this,'getFile']);
        $this->app->put('/writer/changes', [$this, 'putChanges']);
        $this->app->put('/writer/final', [$this, 'putChanges']);
        $this->app->run();
        exit;
    }

    protected function prepare(Request $request, Response $response, array $args, TokenPurpose $purpose): void
    {
        $params = $request->getQueryParams();
        $this->rest_helper->checkAuth($purpose, $params['signature'] ?? '');
        $this->rest_helper->checkAccess();
    }

    /**
     * GET the data for initializing the writer
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->apis->api($component)->writerBridge($this->ass_id, $this->user_id);
            $data[$component] = $bridge->getData(false);
        }
        // create new tokens - these will be replaced in the app
        $response = $this->rest_helper->setNewDataToken($response);
        $response = $this->rest_helper->setNewFileToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET the data for updating the writer
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->apis->api($component)->writerBridge($this->ass_id, $this->user_id);
            $data[$component] = $bridge->getData(true);
        }
        // just
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET a file (well be sent inline)
     */
    public function getFile(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::FILE);

        $component = $args['component'] ?? '';
        $entity = $args['entity'] ?? null;
        $id = $args['id'] ?? null;

        if ($id === null) {
            throw new RestException('No id gven', RestException::NOT_FOUND);
        }
        if ($entity === null) {
            throw new RestException('No entity given', RestException::NOT_FOUND);
        }

        $bridge = $this->apis->api((string) $component)?->writerBridge($this->ass_id, $this->user_id);
        if ($bridge === null) {
            throw new RestException('Component not found', RestException::NOT_FOUND);
        }

        $file_id = $bridge->getFileId((string) $entity, (int) $id);
        if ($file_id === null) {
            throw new RestException('Resource file not found', RestException::NOT_FOUND);
        }

        $this->delivery->sendFile($file_id, Disposition::INLINE);
    }

    /**
     * PUT changes from the writer app
     * Request and response are json arrays: component => entity => change data
     */
    public function putChanges(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $json = [];

        foreach ($this->rest_helper->getJsonData($request) as $component => $component_data) {
            $bridge = $this->apis->api((string) $component)?->writerBridge($this->ass_id, $this->user_id);
            if ($bridge === null) {
                continue;
            }

            foreach ((array) $component_data as $list => $changes) {
                foreach ((array) $changes as $change_data) {
                    $change = new ChangeRequest(
                        (string) $change_data['type'] ?? '',
                        (string) $change_data['key'] ?? '',
                        (int) $change_data['last_change'] ?? 0,
                        ChangeAction::tryFrom($change_data['action'] ?? ''),
                        $change_data['payload'] ?? null
                    );

                    $json[$component][$list][] = $bridge->applyChange($change)->toArray();
                }
            }
        }

        $this->rest_helper->setAlive();
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $json);
    }
}
