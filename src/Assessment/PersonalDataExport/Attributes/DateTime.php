<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class DateTime extends Attribute
{
    private string $format = DATE_W3C;

    public function __construct(string $title, private \DateTimeImmutable $value)
    {
        parent::__construct($title);
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function getValue(): string
    {
        return $this->value->format($this->format);
    }
}