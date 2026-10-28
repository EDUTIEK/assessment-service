<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Edutiek\AssessmentService\Assessment\Authentication\FullService as Tokens;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\User\AuthService;
use Edutiek\AssessmentService\Views\Api\ForRest as ViewsRestApi;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;

class AppStart implements RestService
{
    protected ?int $user_id;
    protected AuthService $users;


    public function __construct(
        protected readonly Frontend $frontend,
        protected readonly SlimApp $app,
        protected readonly ViewsRestApi $views,
        protected readonly SystemApi $system,
        protected readonly Tokens $tokens
    ) {
        $this->users = $system->user();
    }

    public function handle(): never
    {
        $this->app->post('/corrector/start/login', [$this, 'login']);
        $this->app->put('/corrector/start/logout', [$this, 'logout']);
        $this->app->put('/corrector/start/agreement', [$this,'setAgreement']);
        $this->app->get('/corrector/start/assessments', [$this,'getAssessments']);
        $this->app->put('/corrector/start/open', [$this,'openAssessment']);
        $this->app->run();
        exit;
    }

    /**
     * Prepare the handler (except for login requests)
     * - Check the provided token
     * - Get the user id from the token
     * - Optionally check if the user is active
     * - Optionally check if the user agreement is accepted
     */
    private function prepare(Request $request, bool $check_active = true, bool $check_agreement = true): void
    {
        $token = $this->tokens->getByValue($request->getHeaderLine('Xlas-Token'));

        if ($token === null) {
            throw new RestException('current token is not found', RestException::UNAUTHORIZED);
        }
        if (!$this->tokens->checkValidity($token)) {
            throw new RestException('current token is expired', RestException::UNAUTHORIZED);
        }

        $this->user_id = $token->getUserId();

        if ($check_active && !$this->users->isActive($this->user_id)) {
            throw new RestException('user is not active', RestException::UNAUTHORIZED);
        }

        if ($check_agreement && !$this->users->hasAcceptedAgreement($this->user_id)) {
            throw new RestException('user is not active', RestException::UNAUTHORIZED);
        }
    }

    /**
     * Handle a login request
     */
    public function login(Request $request, Response $response, array $args): Response
    {
        $data = [];

        $body = $request->getParsedBody();
        $login = $body['login'];
        $password = $body['password'];

        $this->user_id = $this->users->authenticate($login, $password, null);

        if (empty($user_id)) {
            $data = ['failed_message' => $this->users->failedMessage($login)];
            return $this->setResponse($response, StatusCodeInterface::STATUS_UNAUTHORIZED, $data);
        }

        $user_data = $this->users->getUser($this->user_id);
        $token = $this->tokens->newToken($this->user_id, TokenPurpose::LOGIN);
        $this->tokens->saveToken($token);

        $data = [
            'user_id' => $this->user_id,
            'token' => $token->getToken(),
            'full_name' => $user_data->getFullname(false),
            'agreement' => $this->users->getAgreement($this->user_id),
            'accepted' => $this->users->hasAcceptedAgreement($this->user_id),
        ];

        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * Handle a logout request
     */
    public function logout(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, false, false);
        $this->tokens->deleteToken($this->user_id, TokenPurpose::LOGIN);

        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, []);
    }

    /**
     * Handle a request to set the user agreement
     */
    public function setAgreement(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, true, false);

        $this->users->acceptAgreement($this->user_id);
        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, []);
    }

    /**
     * Get the list of assessments that a user can access
     */
    public function getAssessments(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request);
        $user = $this->users->getUser($this->user_id);

        $formats = $this->system->format($this->user_id);

        $data = [];
        if ($this->frontend == Frontend::CORRECTOR) {
            foreach ($this->views->assessment()->correctorAssessments($this->user_id) as $assessment) {
                $data[] = [
                    'ass_id' => $assessment->getAssId(),
                    'context_id' => $assessment->getContextId(),
                    'parent_title' => $assessment->getParentTitle(),
                    'title' => $assessment->getTitle(),
                    'correction_start' => $formats->date($assessment->getCorrectionStart()?->setTimezone($user->getTimezone())),
                    'correction_end' => $formats->date($assessment->getCorrectionEnd()?->setTimezone($user->getTimezone())),
                    'assigned_gradings' => $assessment->getAssignedGradings(),
                    'finished_gradings' => $assessment->getFinishedGradings()
                ];
            }
        }

        return $this->setResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * Open an assessment
     * - Check if the user is authentified, has accepted the agreement, and has access to the assessment
     * - Initialize the data token
     * Send the credentials as if the app was opened from the backend
     */
    public function openAssessment(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request);

        $data = [];

        // TODO: create and add the required data

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
