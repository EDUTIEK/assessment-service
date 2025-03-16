<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Properties;

use Edutiek\AssessmentService\Assessment\Data\Properties;

interface FullService
{
    public function get(): Properties;
    public function validate(Properties $properties): bool;
    public function save(Properties $properties): void;
}
