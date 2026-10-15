<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class File extends Attribute
{
    private \Closure $transformator;

    public function __construct(string $title, private string $value)
    {
        parent::__construct($title);
        $this->transformator = function (string $value) {return $value;};
    }

    public function setFilenameTransformator(\Closure $transformator)
    {
        $this->transformator = $transformator;
    }

    public function getValue(): string
    {
        return (string) ($this->transformator)($this->value);
    }
}