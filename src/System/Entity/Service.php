<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Entity;

use ReflectionClass;
use ReflectionMethod;
use Edutiek\AssessmentService\System\Api\HasHtml;
use Monolog\DateTimeImmutable;
use DateTime;

class Service implements FullService
{
    /**
     * Cached properties of the given classes: class name => property (PascalCase) => meta data
     *
     * @var array<string, array<string, array{'type': string, 'html': bool, 'null': bool}>>
     */
    private array $properties;


    public function toPrimitives(object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): array
    {
        $primitives = [];
        foreach ($this->getProperties($class) as $name => $meta) {
            $method = 'get' . $name;
            $key = $this->convertCase($name, KeyCase::PASCAL_CASE, $case);
            $primitives[$key] = $this->toPrimitive($entity->$method());
        }
        return $primitives;
    }

    public function fromPrimitives(array $primitives, object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): void
    {
        foreach ($this->getProperties($class) as $name => $meta) {
            $setter = 'set' . $name;
            $entity->$setter($this->fromPrimitive($primitives[$name] ?? null, $meta['type'], $meta['null']));
        }
    }

    public function secure(object $entity, string $class): object
    {
        foreach ($this->getProperties($class) as $name => $meta) {
            $getter = 'get' . $name;
            $setter = 'set' . $name;

            if ($meta['type'] === 'string' || $meta['type'] === '?string') {
                $value = $entity->$getter();
                if (!empty($value)) {
                    if ($meta['html']) {
                        $entity->$setter(strip_tags($value,
                            '<p><div><br><strong><b><em><i><u><ol><ul><li><h1><h2><h3><h4><h5><h6><pre>'));
                    } else {
                        $entity->$setter(strip_tags($value));
                    }
                }

            }
        }
        return $entity;
    }

    /**
     * Get the properties information of a class
     * @see self::$properties
     */
    private function getProperties(string $class): array
    {
        if (!isset($this->properties[$class])) {
            $this->properties[$class] = [];
            $reflection_class = new ReflectionClass($class);
            $getters = $reflection_class->getMethods(ReflectionMethod::IS_ABSTRACT);
            foreach ($getters as $getter) {
                if (str_starts_with($getter->name, 'get')) {
                    $this->properties[$class][ substr($getter->name, 3)] = [
                        'type' => (string) $getter->getReturnType(),
                        'html' => !empty($getter->getAttributes(HasHtml::class)),
                        'null' => $getter->getReturnType()->allowsNull()
                    ];
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
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        return null;
    }

    private function fromPrimitive(mixed $value, string $type, bool $nullable): mixed
    {
        if ($value === null && $nullable) {
            return null;
        }
        else {
            switch ($type) {
                case 'int':
                case '?int':
                    return (int) $value;
                case 'float':
                case '?float':
                    return (float) $value;
                    break;
                case 'bool':
                case '?bool':
                    return (bool) $value;
                    break;
                case 'string':
                case '?string':
                    return (string) $value;
                case 'DateTime':
                case '?DateTime':
                    return (new DateTime())->setTimestamp((int) $value);
                case 'DateTimeImmutable':
                case '?DateTimeImmutable':
                    return (new DateTimeImmutable(false))->setTimestamp((int) $value);
                default:
                    if(enum_exists($type)) {
                        // we support only backed enums with string return types
//                        $type = preg_replace('/[^a-zA-Z0-9\\\\]/', '', $type);
//                        $value = preg_replace('/[^a-zA-Z0-9]/', '', (string) $value);
                        $value = $type::tryFrom($value);
                        // eval('$value=' . $type . '::tryFrom($value);');
                        return $value;
                    }
                    return $value;
            }
        }
    }
}
