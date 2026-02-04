<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\EventHandling\ObserverFactory;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\CommonDispatcher;
use Edutiek\AssessmentService\System\EventHandling\DispatcherFactory;

class ForEvents implements DispatcherFactory
{
    private array $for_assessment = [];
    private array $for_system = [];

    public function __construct(
        /** @var ObserverFactory[] */
        private readonly array $observer_factories
    ) {
    }

    public function assessmentDispatcher(int $ass_id, int $user_id): Dispatcher
    {
        if (!isset($this->for_assessment[$ass_id][$user_id])) {
            $dispatcher = new CommonDispatcher();
            foreach ($this->observer_factories as $observer_factory) {
                $observer = $observer_factory->assessmentObserver($ass_id, $user_id);
                if ($observer !== null) {
                    $dispatcher->addObserver($observer);
                }
            }
            $this->for_assessment[$ass_id][$user_id] = $dispatcher;
        }
        return $this->for_assessment[$ass_id][$user_id];
    }

    public function systemDispatcher(int $user_id): Dispatcher
    {
        if (!isset($this->for_system[$user_id])) {
            $dispatcher = new CommonDispatcher();
            foreach ($this->observer_factories as $observer_factory) {
                $observer = $observer_factory->systemObserver($user_id);
                if ($observer !== null) {
                    $dispatcher->addObserver($observer);
                }
            }
            $this->for_system[$user_id] = $dispatcher;
        }
        return $this->for_system[$user_id];
    }

}
