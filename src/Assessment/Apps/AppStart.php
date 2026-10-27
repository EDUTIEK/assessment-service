<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\Views\Api\ForRest as ViewsRestApi;

class AppStart implements RestService
{
    protected readonly ?int $user_id;

    public function __construct(
        protected readonly Frontend $frontend,
        protected readonly SlimApp $app,
        protected readonly ViewsRestApi $views
    ) {
    }

    public function handle(): never
    {
        $this->app->put('/corrector/start/login', [$this, 'login']);
        $this->app->put('/corrector/start/logout', [$this, 'logout']);
        $this->app->get('/corrector/start/assessments', [$this,'assessments']);
        $this->app->run();
        exit;
    }


    /**
     * Prepare handling the REST call
     * This must be called by all functions assigned to routes
     * It can't be done in handle() because the TokenPurpose depends on the function
     */
    protected function prepare(Request $request, Response $response, array $args): void
    {
        $params = $request->getQueryParams();
    }

    public function login(Request $request, Response $response, array $args): Response
    {
        $data = [];
        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    public function logout(Request $request, Response $response, array $args): Response
    {
        $data = [];
        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    public function assessments(Request $request, Response $response, array $args): Response
    {
        $data = [];

        if ($this->user_id !== null) {
            if ($this->frontend == Frontend::CORRECTOR) {
                $assessments = $this->views->assessment()->correctorAssessments($this->user_id);
            }
        }

        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }


    /**
     * Modify the response with a status code and json return
     * @param string|array $data
     */
    private function setResponse(Response $response, int $status, $data = []): Response
    {
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('xlasTime', (string) time())
            ->withStatus($status);
        $response->getBody()->write(json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
        return $response;
    }

}
