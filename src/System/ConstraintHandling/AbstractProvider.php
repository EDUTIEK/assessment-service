<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

use Closure;

/**
 * Abstract implementation of a constraint provider
 * The real providers just need to implement a constrictor that registers their available constraints
 */
abstract class AbstractProvider implements Provider
{
    /** @var array<class-string<Action>, class-string<Constraint>[]> */
    private array $action_constraints = [];

    /** @var array<class-string<Constraint>, Closure(): Constraint> */
    private $constraint_inits = [];

    /** @var array<class-string<Constraint>, Constraint> */
    private $constraint_instances = [];

    /**
     * @param class-string<Constraint> $constraint constraint class name
     * @param Closure(): Constraint $init constraint creation function
     */
    public function registerConstraint(string $constraint, Closure $init): void
    {
        array_map(
            fn(string $action) => $this->action_constraints[$action][] = $constraint,
            $constraint::actions()
        );
        unset($this->constraint_instances[$constraint]);
        $this->constraint_inits[$constraint] = $init;
    }

    public function check(Action $action, ResultCollection $collection): void
    {
        array_map(
            fn(Constraint $constraint) => $constraint->check($action, $collection),
            $this->getConstraints($action)
        );
    }

    /** @return Constraint[] */
    private function getConstraints(Action $action): array
    {
        return array_map(
            function (string $constraint_class) {
                return $this->constraint_instances[$constraint_class] ?? $this->constraint_inits[$constraint_class]();
            },
            $this->action_constraints[$action::class] ?? []
        );
    }
}
