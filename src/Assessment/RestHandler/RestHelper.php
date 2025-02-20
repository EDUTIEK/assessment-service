<?php

namespace Edutiek\AssessmentService\Assessment\RestHandler;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as Authentication;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as Permissions;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

readonly class RestHelper
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private int $user_id,
        private Authentication $auth,
        private Permissions $permissions,
        private Repositories $repos,
        private UserReadService $user_service,
    ) {
    }

    /**
     * Check the authentication of the rwquesr
     */
    public function checkAuth(TokenPurpose $purpose, string $signature): void
    {
        $token = $this->auth->getToken($this->user_id, $purpose);
        if ($token === null) {
            throw new RestException(RestException::UNAUTHORIZED, 'current token is not found');
        }
        if (!$this->auth->checkValidity($token)) {
            throw new RestException(RestException::UNAUTHORIZED, 'current token is expired');
        }
        if (!$this->auth->checkSignature($token, $signature)) {
            throw new RestException(RestException::UNAUTHORIZED, 'signature is wrong');
        }
    }

    /**
     * Check if the user has access to the assessment
     */
    public function checkAccess()
    {
        if ($this->user_service->getUser($this->user_id) === null) {
            throw new RestException(RestException::NOT_FOUND, 'user not found');
        }
        if (!$this->repos->properties()->exists($this->ass_id, $this->context_id)) {
            throw new RestException(RestException::NOT_FOUND, 'assessment not found');
        }
        if (!$this->permissions->canDoRestCall()) {
            throw new RestException(RestException::FORBIDDEN, 'REST call not allowed');
        }
    }

    /**
     * Parses the JSON body from the incoming HTTP request if the `Content-Type`
     * header indicates a JSON payload. Converts the body into an associative
     * array and attaches it to the request.
     *
     * @param Request $request The HTTP request instance to process.
     */
    public function parseJsonBody(Request $request)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }
    }

    /**
     * Set a new expiration time for the data token and set it in the response
     */
    protected function refreshDataToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::DATA);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::DATA));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayDataToken', $token->getToken());
    }

    /**
     * Generate a new data token and set it in the response
     */
    protected function setNewDataToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::DATA);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::DATA));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayDataToken', $token->getToken());
    }

    /**
     * Generate a new file token and set it in the response
     */
    protected function setNewFileToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::FILE);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::FILE));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayFileToken', $token->getToken());
    }

    /**
     * Modify the response with a status code and json return
     * @param string|array $json
     */
    protected function setResponse(Response $response, int $status, $json = []): Response
    {
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('LongEssayTime', (string) time())
            ->withStatus($status);
        $response->getBody()->write(json_encode($json));
        return $response;
    }
}
