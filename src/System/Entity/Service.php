<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Entity;

use ReflectionClass;
use ReflectionMethod;

class Service implements FullService
{
    /**
     * Properties of the given classes: class name => property (PascalCase) => getter return type
     * @todo: this array can be created and loaded from artefacts to avoid reflection calls for each request
     *
     * @var array<string, array<string, string>>
     */
    private array $properties;


    public function toPrimitives(object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): array
    {
        $primitives = [];
        foreach ($this->getProperties($class) as $name => $type) {
            $method = 'get' . $name;
            $key = $this->convertCase($name, KeyCase::PASCAL_CASE, KeyCase::SNAKE_CASE);
            $primitives[$key] = $this->toPrimitive($entity->$method());
        }
        return $primitives;
    }

    public function fromPrimitives(array $primitives, object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): void
    {
        // TODO: Implement fromPrimitives() method.
    }

    /**
     * @param string $class
     * @return array<string, string>    property name (PascalCase) => property type
     */
    private function getProperties(string $class): array
    {
        if (!isset($this->properties[$class])) {
            $this->properties[$class] = [];
            $reflection_class = new ReflectionClass($class);
            $getters = $reflection_class->getMethods(ReflectionMethod::IS_ABSTRACT);
            foreach ($getters as $getter) {
                if (str_starts_with($getter->name, 'get')) {
                    $this->properties[$class][$getter->name] = $getter->getReturnType()->getName();
                }
            }
        }
        return $this->properties[$class];
    }

    private function convertCase(string $name, KeyCase $from, KeyCase $to): string
    {
        if ($from === KeyCase::PASCAL_CASE && $to === KeyCase::SNAKE_CASE) {
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        } elseif ($from === KeyCase::SNAKE_CASE && $to === KeyCase::PASCAL_CASE) {
            return str_replace('_', '', ucwords($name, '_'));
        }

        return $name;
    }

    private function toPrimitive(mixed $value): mixed
    {
        if (is_scalar($value)) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        return null;
    }
}
