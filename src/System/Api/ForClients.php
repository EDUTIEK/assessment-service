<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\FullService;
use Edutiek\AssessmentService\System\Config\Service;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;


class ForClients
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    public function config(): FullService {
        return self::$instances[FullService::class] ??= new Service(
            $this->dependencies->configRepo()
        );
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

}
