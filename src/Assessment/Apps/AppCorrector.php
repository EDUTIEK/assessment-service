<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\Config\Frontend;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AppCorrector extends BaseApp implements RestService
{
    protected Frontend $frontend = Frontend::CORRECTOR;

    public function handle(): never
    {
        $this->app->get('/corrector/data', [$this,'getData']);
        $this->app->get('/corrector/item/{task_id}/{writer_id}', [$this,'getItem']);
        $this->app->get('/corrector/file/{component}/{entity}/{id}', [$this,'getFile']);
        $this->app->put('/corrector/changes', [$this, 'putChanges']);
        $this->app->post('/corrector/file/{task_id}/{writer_id}', [$this,'postFile']);
        $this->app->run();
        exit;
    }

    /**
     * Prepare handling the REST call
     * This must be called by all functions assigned to routes
     * It can't be done in handle() because the TokenPurpose depends on the function
     */
    protected function prepare(Request $request, Response $response, array $args, TokenPurpose $purpose): void
    {
        parent::prepare($request, $response, $args, $purpose);

        // todo: check if user is corrector or has permission to maintain correctors
    }

    /**
     * GET the data for updating the status in the app
     */
    public function getItem(Request $request, Response $response, array $args): Response
    {
        $task_id = (int) ($args['task_id'] ?? 0);
        $writer_id = (int) ($args['writer_id'] ?? 0);

        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $data = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            $bridge = $this->getBridge($component);
            if ($bridge instanceof AppCorrectorBridge) {
                $data[$component] = $bridge->getItem($task_id, $writer_id);
            }
        }
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    public function postFile(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, $response, $args, TokenPurpose::DATA);

        $task_id = (int) ($args['task_id'] ?? 0);
        $writer_id = (int) ($args['writer_id'] ?? 0);
        $component = 'task';
        $entity = 'summary';

        if ($entity === null) {
            throw new RestException('No entity given', RestException::NOT_FOUND);
        }
        $bridge = $this->getBridge((string) $component);
        if ($bridge === null) {
            throw new RestException("Component $component not found", RestException::NOT_FOUND);
        }

        $file = $request->getUploadedFiles()['file'] ?? null;
        if ($file?->getError() === UPLOAD_ERR_OK) {
            $bridge = $this->getBridge((string) $component);
            $id = $bridge->processUploadedFile($file, $task_id, $writer_id);
        }

        $json = [
            'id' => $id,
        ];

        $this->rest_helper->setAlive();
        $this->rest_helper->extendDataToken($response);
        return $this->rest_helper->setResponse($response, StatusCodeInterface::STATUS_OK, $json);
    }
}
