<?php

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

interface ValidationErrorStore
{
    public function addValidationError(ValidationError $error): void;

    public function getValidationErrors(): array;
}