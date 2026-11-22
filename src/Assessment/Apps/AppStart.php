<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Auth\FullService as AuthService;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\Rest\RestHandler;
use Edutiek\AssessmentService\Views\Api\ForRest as ViewsRestApi;
use Edutiek\AssessmentService\System\Rest\RestHelper;
use Edutiek\AssessmentService\System\Rest\RestException;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use PharIo\Manifest\Exception;

class AppStart implements RestHandler
{
    protected ?int $user_id;
    protected AuthService $auth;
    protected UserService $users;
    protected RestHelper $rest;

    public function __construct(
        protected readonly Frontend $frontend,
        protected readonly SlimApp $app,
        protected readonly Repositories $repos,
        protected readonly ViewsRestApi $views,
        protected readonly SystemApi $system,
    ) {
        $this->auth = $system->auth();
        $this->users = $system->user();
        $this->rest = $system->restHelper();
    }

    public function handle(): never
    {
        $this->app->post('/corrector/start/login', [$this, 'login']);
        $this->app->put('/corrector/start/logout', [$this, 'logout']);
        $this->app->put('/corrector/start/agreement', [$this,'setAgreement']);
        $this->app->get('/corrector/start/assessments', [$this,'getAssessments']);
        $this->app->post('/corrector/start/password/request', [$this, 'requestPasswordChange']);
        $this->app->post('/corrector/start/password/code', [$this, 'checkPasswordChangeCode']);
        $this->app->post('/corrector/start/password/change', [$this, 'changePassword']);
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
        $this->user_id = $this->rest->authenticate();

        if ($check_active && !$this->auth->isActive($this->user_id)) {
            throw new RestException('user is not active', RestException::UNAUTHORIZED);
        }

        if ($check_agreement && !$this->auth->hasAcceptedAgreement($this->user_id)) {
            throw new RestException('user has not accepted the agreement', RestException::UNAUTHORIZED);
        }
    }

    /**
     * Handle a login request
     */
    public function login(Request $request, Response $response, array $args): Response
    {
        $data = [];

        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        $this->user_id = $this->auth->authenticate($login, $password, null);

        if (empty($this->user_id)) {
            $data = ['failed_message' => $this->auth->failedMessage($login)];
            return $this->rest->setJsonResponse($response, StatusCodeInterface::STATUS_UNAUTHORIZED, $data);
        }

        $user_data = $this->users->getUser($this->user_id);
        $data = [
            'user_id' => $this->user_id,
            'login' => $user_data->getLogin(),
            'full_name' => $user_data->getFullname(false),
            'agreement' => $this->auth->getAgreement($this->user_id),
            'accepted' => $this->auth->hasAcceptedAgreement($this->user_id),
        ];

        $this->rest->createToken($this->user_id);
        return $this->rest->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * Handle a logout request
     */
    public function logout(Request $request, Response $response, array $args): Response
    {
        try {
            $this->prepare($request, false, false);
            $this->rest->deleteToken();
        } catch (RestException $e) {
        }

        return $this->rest->setJsonResponse($response, StatusCodeInterface::STATUS_OK, []);
    }

    /**
     * Handle a request to set the user agreement
     */
    public function setAgreement(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request, true, false);

        $this->auth->acceptAgreement($this->user_id);
        return  $this->rest->setJsonResponse($response, StatusCodeInterface::STATUS_OK, []);
    }

    /**
     * Get the list of assessments that a user can access
     */
    public function getAssessments(Request $request, Response $response, array $args): Response
    {
        $this->prepare($request);
        $user = $this->users->getUser($this->user_id);
        $permissions = $this->repos->permissions();

        $formats = $this->system->format($this->user_id);

        $data = [];
        if ($this->frontend == Frontend::CORRECTOR) {
            foreach ($this->views->assessment()->correctorAssessments($this->user_id) as $assessment) {
                if ($permissions->canRead($assessment->getAssId(), $assessment->getContextId(), $this->user_id)) {
                    $data[] = [
                        'ass_id' => $assessment->getAssId(),
                        'context_id' => $assessment->getContextId(),
                        'parent_title' => $assessment->getParentTitle(),
                        'title' => $assessment->getTitle(),
                        'correction_start_ts' => $assessment->getCorrectionStart()?->getTimestamp(),
                        'correction_end_ts' => $assessment->getCorrectionEnd()?->getTimestamp(),
                        'correction_start' => $formats->date($assessment->getCorrectionStart()?->setTimezone($user->getTimezone())),
                        'correction_end' => $formats->date($assessment->getCorrectionEnd()?->setTimezone($user->getTimezone())),
                        'assigned_gradings' => $assessment->getAssignedGradings(),
                        'finished_gradings' => $assessment->getFinishedGradings()
                    ];
                }
            }
        }

        return  $this->rest->setJsonResponse($response, StatusCodeInterface::STATUS_OK, $data);
    }

    /**
     * Send an email with the code to change a password
     */
    public function requestPasswordChange(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody();
            $email = $body['email'];
            $this->auth->requestPasswordChange($email, $this->frontend);
            $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_OK);
        } catch (Exception $e) {
            $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

    }

    /**
     * Check the code to change a password
     */
    public function checkPasswordChangeCode(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $code = $body['code'] ?? '';

        if (!$this->auth->checkPasswordChangeCode($login, $code)) {
            $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_OK);
    }

    /**
     * Handle a password change request
     */
    public function changePassword(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        $code = $body['code'];
        $login = $body['login'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($code) || empty($login)) {
            try {
                $user_id = $this->rest->authenticate();
                $login = $this->users->getUser($user_id)->getLogin();
            } catch (RestException $exception) {
                $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
            }
        } elseif (!$this->auth->checkPasswordChangeCode($login, $code)) {
            $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        $result = $this->auth->changePassword($login, $password);

        if ($result->isFailed()) {
            $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_BAD_REQUEST, implode('; ', $result->failures()));
        }

        $this->rest->sendPlainResponse(StatusCodeInterface::STATUS_OK);
    }
}
