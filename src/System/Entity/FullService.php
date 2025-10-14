<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Entity;

interface FullService
{
    /**
     * Converts the properties of an entity to an array of primitive types (null, string, int, float, bool)
     * Supported property types are null, string, int, float, bool, DateTime, DateTimeImmutable, BackedEnum with string return type)
     * - values are taken from entity methods which are defined as abstract getters in the given class
     * - array keys are taken from the getter names (without 'get') and converted to the given case
     * - datetime values are converted to unix timestamps
     * - null values are kept
     * @param object $entity    object from which the values are taken
     * @param string $class     name of the class that defines the abstract getters and setters
     * @param KeyCase $case     case of the generated array keys
     */
    public function toPrimitives(object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): array;

    /**
     * Sets the properties of an entity from an array of primitive types (null, string, int, float, bool)
     * Supported property types are null, string, int, float, bool, DateTime, DateTimeImmutable, BackedEnum with string return type)
     * - values are set by methods which are defined as abstract setters in the given class
     * - array keys have to be in given case and correspond to the setter names without 'set'
     * - datetime values have to be provided as unix timestamps
     * - null values are kept for nullable properties, otherwise default values are used
     * - string values are cleaned up with strip_tags
     *
     * @param object  $entity object in which the values should be set
     * @param string  $class  name of the class that defines the abstract getters and setters
     * @param KeyCase $case   case of the provided array keys
     */
    public function fromPrimitives(array $primitives, object $entity, string $class, KeyCase $case = KeyCase::SNAKE_CASE): void;

    /**
     * Make the values of the entity secure against xss
     * All HTML tags are stripped from string properties
     * except those where the abstract getter is defined with the attribute #[HasHtml]
     * These properties will have a set of allowed HTML tags
     *
     * @param object  $entity object which should be secured
     * @param string  $class  name of the class that defines the abstract getters and setters
     */
    public function secure(object $entity, string $class): object;


    /**
     * Convert array values to primitives
     */
    public function arrayToPrimitives(array $array): array;
}
