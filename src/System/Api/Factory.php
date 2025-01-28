<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

class Factory
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    /**
     * Get the API for client systems
     */
    public function forClients() : ForClients
    {
        return self::$instances[ForClients::class] ??= new ForClients($this->dependencies);
    }

    /**
     * Get the API for peer services
     */
    public function forServices() : ForServices
    {
        return self::$instances[ForServices::class] ??= new ForServices($this->dependencies);
    }

}
