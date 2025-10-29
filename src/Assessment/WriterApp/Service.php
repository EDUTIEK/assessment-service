<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestException;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestService;
use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridge;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager as TasksManager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeApiFactory;
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
        private TasksManager $tasks_manager,
        private App $app,
        private WriterBridge $ass_bridge,
        private WriterBridge $task_bridge,
        private TypeApiFactory $types,
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
        $this->app->put('/writer/final', [$this,'putFinal']);
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
        foreach ($this->getComponents() as $component) {
            $data[$component] = $this->getBridge($component)->getData();
        }
        $response = $this->rest_helper->setNewDataToken($response);
        $response = $this->rest_helper->setNewFileToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET the data for updating the writer
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        return $response;
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

        $bridge = $this->getBridge($component);
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
            $bridge = $this->getBridge((string) $component);
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
        $this->rest_helper->refreshDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $json);
    }

    /**
     * PUT the final content
     * "final" means that the writer is intentionally closed
     * That could be an interruption or the authorized submission
     * the content is only saved when the essay is not yet authorized
     */
    public function putFinal(Request $request, Response $response, array $args): Response
    {
        return $response;
    }

    /**
     * Get all relevant components for the tasks of the assessment
     */
    private function getComponents()
    {
        $components = ['Assessment', 'Task'];
        foreach ($this->tasks_manager->all() as $task) {
            $components[] = $task->getTaskType()->component();
        }
        return array_unique($components);
    }

    /**
     * Get the responsible bridge by a component string
     * Components may be given in lower case, e.g. in REST paths
     */
    private function getBridge(string $component): ?WriterBridge
    {
        $compare = strtolower($component);
        switch ($compare) {
            case 'assessment':
                return $this->ass_bridge;
            case 'task':
                return $this->task_bridge;
            default:
                foreach ($this->tasks_manager->all() as $task) {
                    if (strtolower($task->getTaskType()->component()) == $compare) {
                        return $this->types->api($task->getTaskType())
                            ->writerBridge($this->ass_id, $this->user_id);
                    }
                }
        }
        return null;
    }
}
