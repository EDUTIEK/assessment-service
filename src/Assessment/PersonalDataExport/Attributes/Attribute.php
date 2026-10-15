<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

abstract class Attribute
{

    public function __construct(private string $title)
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    abstract public function getValue(): string|int|bool|float;
}