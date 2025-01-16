<?php

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\ReadService;
use Edutiek\AssessmentService\System\Config\Service;

class ForServices
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    public function config(): ReadService {
        return self::$instances[ReadService::class] ??= new Service(
            $this->dependencies->configRepo()
        );
    }
}