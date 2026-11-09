<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Api\ComponentApiFactory;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Disposition;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Rest\RestHelper;
use Edutiek\AssessmentService\System\Rest\RestHandler;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as Permissions;
use Edutiek\AssessmentService\System\Rest\RestException;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

abstract class BaseApp implements RestHandler
{
    /**
     * Needs to be set by the child class
     */
    protected Frontend $frontend;

    public function __construct(
        protected readonly int $ass_id,
        protected readonly int $context_id,
        protected readonly int $user_id,
        protected readonly Repositories $repos,
        protected readonly Permissions $permissions,
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
     */
    protected function prepare(Request $request, Response $response, array $args): void
    {
        $params = $request->getQueryParams();

        if (!$this->repos->properties()->exists($this->ass_id, $this->context_id)) {
            throw new RestException('assessment not found', RestException::NOT_FOUND);
        }
        if (!$this->permissions->canDoRestCall()) {
            throw new RestException('REST call not allowed', RestException::FORBIDDEN);
        }
    }

    /**
     * GET the data for initializing the app
     */
    public function getData(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            if ($bridge === null) {
                continue;
            }
            $data[$component] = $bridge->getData(false);
        }

        return $this->rest_helper->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET the data for updating the status in the app
     */
    public function getUpdate(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            if ($bridge === null) {
                continue;
            }
            $data[$component] = $bridge->getData(true);
        }

        return $this->rest_helper->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * GET a file (will be sent with the requested disposition
     */
    public function getFile(Request $request, Response $response, array $args): void
    {
        $this->prepare($request, $response, $args);

        $component = $args['component'] ?? '';
        $entity = $args['entity'] ?? null;
        $id = $args['id'] ?? null;
        $disposition = Disposition::tryFrom($args['disposition'] ?? '') ?? Disposition::INLINE;

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

        $info = $bridge->getFileInfo((string) $entity, (int) $id);
        if ($info === null) {
            throw new RestException("File for entity $entity with id $id not found", RestException::NOT_FOUND);
        }

        $this->delivery->sendFile($info->getId(), $disposition, $info);
    }

    /**
     * PUT changes coming from the app
     * Request and response are json arrays: component => entity => change data
     */
    public function putChanges(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args);

        $json = [];

        foreach ($this->rest_helper->getJsonData($request) as $component => $component_data) {
            $bridge = $this->getBridge((string) $component);
            if ($bridge === null) {
                continue;
            }

            foreach ((array) $component_data as $type => $list) {

                $changes = array_map(fn(array $data) => new ChangeRequest(
                    (string) $data['type'] ?? '',
                    (string) $data['key'] ?? '',
                    (int) $data['last_change'] ?? 0,
                    ChangeAction::tryFrom($data['action'] ?? ''),
                    $data['payload'] ?? null
                ), $list);

                $json[$component][$type] = array_map(
                    fn(ChangeResponse $response) => $response->toArray(),
                    $bridge->applyChanges($type, $changes)
                );
            }
        }

        return $this->rest_helper->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $json);
    }

    /**
     * Get the bridge of a component to handle the data transfer
     */
    protected function getBridge(string $component): ?AppBridge
    {
        $api = $this->apis->api($component);
        $params = $this->rest_helper->getParams();

        return match($this->frontend) {
            Frontend::WRITER => $api?->writerBridge($this->ass_id, $this->user_id),
            Frontend::CORRECTOR => $api?->correctorBridge($this->ass_id, $this->user_id)?->setAdmin(
                (($params['as_admin'] ?? '') === 'true') && $this->permissions->canMaintainCorrectors()
            )
        };
    }
}
