<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class Boolean extends Attribute
{
    public function __construct(string $title, private readonly bool $value)
    {
        parent::__construct($title);
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}