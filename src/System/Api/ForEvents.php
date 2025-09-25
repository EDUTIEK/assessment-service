<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\EventHandling\ObserverFactory;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\CommonDispatcher;
use Edutiek\AssessmentService\System\EventHandling\DispatcherFactory;

class ForEvents implements DispatcherFactory
{
    private array $instances = [];

    public function __construct(
        /** @var ObserverFactory[] */
        private readonly array $observer_factories
    ) {
    }

    public function dispatcher(int $ass_id, int $user_id): Dispatcher
    {
        if (!isset($this->instances[$ass_id][$user_id])) {
            $dispatcher = new CommonDispatcher();
            foreach ($this->observer_factories as $observer_factory) {
                $dispatcher->addObserver($observer_factory->observer($ass_id, $user_id));
            }
            $this->instances[$ass_id][$user_id] = $dispatcher;
        }
        return $this->instances[$ass_id][$user_id];
    }
}
