<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class Number extends Attribute
{
    public function __construct(string $title, private float|int $value)
    {
        parent::__construct($title);
    }

    public function getValue(): float|int
    {
        return $this->value;
    }
}