<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Api\ComponentApiFactory;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Disposition;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Edutiek\AssessmentService\System\Config\Frontend;

abstract class BaseApp implements RestService
{
    /**
     * Needs to be set by the child class
     */
    protected Frontend $frontend;

    public function __construct(
        protected readonly int $ass_id,
        protected readonly int $user_id,
        protected readonly RestHelper $rest_helper,
        protected readonly ComponentApiFactory $apis,
        protected readonly SlimApp $app,
        protected readonly Delivery $delivery
    ) {
    }

    /**
     * Define the routes and their callback functions and run the app
     * The callback function getData(), getUpdate(), getFile(), and putChanges() are implemented here
     */
    abstract public function handle(): never;

    /**
     * Prepare handling the REST call
     * This must be called by all functions assigned to routes
     * It can't be done in handle() because the TokenPurpose depends on the function
     */
    protected function prepare(Request $request, Response $response, array $args, TokenPurpose $purpose): void
    {
        $params = $request->getQueryParams();
        $this->rest_helper->checkAuth($purpose, $params['signature'] ?? '');
        $this->rest_helper->checkAccess();
    }

    /**
     * GET the data for initializing the app
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            if ($bridge === null) {
                continue;
            }
            $data[$component] = $bridge->getData(false);
        }
        // create new tokens - these will be replaced in the app
        $response = $this->rest_helper->setNewDataToken($response);
        $response = $this->rest_helper->setNewFileToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET the data for updating the status in the app
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            if ($bridge === null) {
                continue;
            }
            $data[$component] = $bridge->getData(true);
        }

        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET a file (will be sent inline)
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

        $bridge = $this->getBridge((string) $component);
        if ($bridge === null) {
            throw new RestException("Component $component not found", RestException::NOT_FOUND);
        }

        $file_id = $bridge->getFileId((string) $entity, (int) $id);
        if ($file_id === null) {
            throw new RestException("File for entity $entity with id $id not found", RestException::NOT_FOUND);
        }

        $this->delivery->sendFile($file_id, Disposition::INLINE);
    }

    /**
     * PUT changes coming from the app
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
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $json);
    }

    /**
     * Get the bridge of a component to handle the data transfer
     */
    protected function getBridge(string $component): ?AppBridge
    {
        $api = $this->apis->api($component);
        return match($this->frontend) {
            Frontend::WRITER => $api?->writerBridge($this->ass_id, $this->user_id),
            Frontend::CORRECTOR => $api?->correctorBridge($this->ass_id, $this->user_id)
        };
    }
}
