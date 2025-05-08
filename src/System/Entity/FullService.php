<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Entity;

interface FullService
{
    /**
     * Converts the properties of an entity to an array of primitive types (null, string, int, float, bool)
     * - values are taken from entity methods which are defined as abstract getters in the given class
     * - array keys are taken from the getter names (without 'get') and converted to the given case
     * - datetime values are converted to unix timestamps
     * - null values are kept
     * @param object $entity    object from which the values are taken
     * @param string $class     name of the class that defines the abstract getters
     * @param KeyCase $case     case of the generated array keys
     */
    public function toPrimitives(object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): array;

    /**
     * Sets the properties of an entity from an array of primitive types (null, string, int, float, bool)
     * - values are set by methods which are defined as abstract setters in the given class
     * - array keys have to be in given case and correspond to the setter names without 'set'
     * - datetime values have to be provided as unix timestamps
     * - null values are kept for nullable properties, otherwise default values are used
     *
     * @param object  $entity object in which the values should be set
     * @param string  $class  name of the class that defines the abstract getters
     * @param KeyCase $case   case of the provided array keys
     */
    public function fromPrimitives(array $primitives, object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): void;
}
