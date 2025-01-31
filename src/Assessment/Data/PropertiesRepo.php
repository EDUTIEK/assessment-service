<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface PropertiesRepo
{
    public function one(int $ass_id): ?Properties;
    public function save(Properties $properties): void;
}
