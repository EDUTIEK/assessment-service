<?php

namespace Edutiek\AssessmentService\System\Api;

class Factory
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    public function forClients() : ForClients
    {
        return self::$instances[ForClients::class] ??= new ForClients($this->dependencies);
    }

    public function forServices() : ForServices
    {
        return self::$instances[ForServices::class] ??= new ForServices($this->dependencies);
    }

}