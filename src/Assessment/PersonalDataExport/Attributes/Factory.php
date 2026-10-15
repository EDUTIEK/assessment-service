<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes;

class Factory
{
    public function boolean(string $title, bool $value): \Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Boolean
    {
        return new Boolean($title, $value);
    }

    public function dateTime(string $title, \DateTimeImmutable $value): DateTime
    {
        return new DateTime($title, $value);
    }

    public function file(string $title, string $value): File
    {
        return new File($title, $value);
    }

    public function number(string $title, float|int $value): Number
    {
        return new Number($title, $value);
    }

    public function text(string $title, string $value): Text
    {
        return new Text($title, $value);
    }
}