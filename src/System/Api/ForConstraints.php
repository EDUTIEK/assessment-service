<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\ConstraintHandling\CollectorFactory;
use Edutiek\AssessmentService\System\ConstraintHandling\CommonCollector;
use Edutiek\AssessmentService\System\ConstraintHandling\ProviderFactory;

class ForConstraints implements CollectorFactory
{
    private array $instances = [];

    public function __construct(
        /** @var ProviderFactory[] */
        private readonly array $provider_factories
    ) {
    }

    public function collector(int $ass_id, int $user_id): Collector
    {
        if (!isset($this->instances[$ass_id][$user_id])) {
            $collector = new CommonCollector();
            foreach ($this->provider_factories as $provider_factory) {
                $collector->addProvider($provider_factory->provider($ass_id, $user_id));
            }
            $this->instances[$ass_id][$user_id] = $collector;
        }
        return $this->instances[$ass_id][$user_id];
    }
}
