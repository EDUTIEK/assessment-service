<?php

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\FullService;
use Edutiek\AssessmentService\System\Config\Service;

class ForClients
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    public function config(): FullService {
        return self::$instances[FullService::class] ??= new Service($this->dependencies);
    }
}