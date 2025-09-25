<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\System\EventHandling\ObserverFactory;
use Edutiek\AssessmentService\EssayTask\EventHandling\Observer;

class ForEvents implements ObserverFactory
{
    private array $instances = [];

    public function __construct(
        private readonly Internal $internal
    ) {
    }


    public function observer(int $ass_id, int $user_id): Observer
    {
        return $this->instances[Observer::class] ??= new Observer(
            $ass_id,
            $user_id,
            $this->internal
        );
    }
}
