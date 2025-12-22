<?php

namespace Edutiek\AssessmentService\Assessment\Data;

interface ValidationErrorStore
{
    public function addValidationError(ValidationError $error): void;

    public function getValidationErrors(): array;
}