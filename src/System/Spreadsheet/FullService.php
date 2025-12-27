<?php

namespace Edutiek\AssessmentService\System\Spreadsheet;

interface FullService
{
    public function dataFromFile(string $id): array;
}