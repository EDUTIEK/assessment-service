<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

class Factory
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    /**
     * Get the API for client systems
     */
    public function forClients(): ForClients
    {
        return $this->instances[ForClients::class] ??= new ForClients($this->dependencies, $this->internal());
    }

    /**
     * Get the API for peer services
     */
    public function forServices(): ForServices
    {
        return $this->instances[ForServices::class] ??= new ForServices($this->dependencies, $this->internal());
    }

    private function internal(): Internal
    {
        return $this->instances[Internal::class] ??= new Internal($this->dependencies);
    }
}
