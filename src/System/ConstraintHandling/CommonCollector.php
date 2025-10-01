<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

use Closure;

/**
 * Common results collector
 */
class CommonCollector implements Collector
{
    /** @var Provider[] */
    private array $providers = [];

    public function addProvider(Provider $provider): void
    {
        $this->providers[] = $provider;
    }

    public function removeProvider(Provider $provider): void
    {
        $key = array_search($provider, $this->providers, true);
        if ($key !== false) {
            unset($this->providers[$key]);
        }
    }

    public function check(Action $action): Result
    {
        $collection = new ResultCollection();
        array_map(
            fn(Provider $provider) => $provider->check($action, $collection),
            $this->providers
        );
        return $collection->result();
    }
}
