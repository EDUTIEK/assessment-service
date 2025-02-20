<?php

namespace Edutiek\AssessmentService\Assessment\RestHandler;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use GuzzleHttp\Psr7\Response;
use Slim\App;
use Throwable;

class Service implements FullService
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

    public function run(): void
    {
        try {
            $this->context->initCall($this->ass_id, $this->context_id, $this->user_id);

            $this->loadParameters();


            switch ($this->module) {
                case self::MODULE_WRITER:
                    $this->internal->writer($this->ass_id, $this->context_id, $this->user_id)->run();
                    break;

                case self::MODULE_CORRECTOR:
                    $this->internal->corrector($this->ass_id, $this->context_id, $this->user_id)->run();
                    break;

                default:
                    throw new RestException('Module not found', RestException::FORBIDDEN);
            }
        } catch (RestException $e) {
            $this->context->sendResponse($e->getCode(), $e->getMessage());
        } catch (Throwable $e) {
            $this->context->sendResponse(RestException::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }


    private function loadParameters()
    {
        $params = $this->context->getParams();
        foreach (['ass_id', 'context_id', 'user_id'] as $key) {
            if (!isset($params[$key])) {
                throw new RestException("key $key is missing", RestException::NOT_FOUND);
            } else {
                $this->$key = (int) $params[$key];
            }
        }

        $parts = explode('/', $this->context->getRoute());
        $this->module = $parts[0] ?? '';
    }
}
