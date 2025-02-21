<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use GuzzleHttp\Psr7\Response;
use Slim\App;
use Throwable;

class Service implements RestService
{
    private const MODULE_WRITER = 'writer';
    private const MODULE_CORRECTOR = 'corrector';

    private string $module;
    private int $ass_id;
    private int $context_id;
    private int $user_id;

    public function __construct(
        private readonly RestContext $context,
        private readonly Internal $internal
    ) {
    }

    /**
     * Handle a REST call
     */
    public function handle(): never
    {
        try {
            $this->initProperties();
            $this->context->initCall($this->ass_id, $this->context_id, $this->user_id);

            switch ($this->module) {
                case self::MODULE_WRITER:
                    $this->internal->writer($this->ass_id, $this->context_id, $this->user_id)->handle();
                    break;

                case self::MODULE_CORRECTOR:
                    $this->internal->corrector($this->ass_id, $this->context_id, $this->user_id)->handle();
                    break;
            }
        } catch (RestException $e) {
            $this->context->sendResponse($e->getCode(), $e->getMessage());
        } catch (Throwable $e) {
            $this->context->sendResponse(RestException::INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        $this->context->sendResponse(RestException::NOT_IMPLEMENTED, "Module '{$this->module}' not found");
    }

    /**
     * Init the object properties from the REST call
     */
    private function initProperties()
    {
        $params = $this->context->getParams();
        foreach (['ass_id', 'context_id', 'user_id'] as $key) {
            if (!isset($params[$key])) {
                throw new RestException("Query parameter $key is missing", RestException::NOT_FOUND);
            }
        }
        $this->ass_id = (int) $params['ass_id'];
        $this->context_id = (int) $params['context_id'];
        $this->user_id = (int) $params['user_id'];

        $parts = explode('/', $this->context->getRoute());
        $this->module = $parts[0] ?? '';
    }
}
