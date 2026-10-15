<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class Text extends Attribute
{
    public function __construct(string $title, private string $value)
    {
        parent::__construct($title);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}