<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use ReflectionClass;

trait Entity
{


    /**
     * Converts the entity to an array of primitive data types (null, bool, int, float, string)
     */
    public function toPrimitives(): array
    {
        $values = [];
        $r = new ReflectionClass(__CLASS__);
        foreach($r->getMethods(\ReflectionMethod::IS_ABSTRACT) as $method) {
            $method_name = $method->getName();
            if (str_starts_with($method_name, 'get')) {
                $key = substr($method_name, 3);
                $values[$key] = $this->$method_name();
            }
        }
        return $values;
    }

    public static function fromPrimitives(): self
    {

    }

}