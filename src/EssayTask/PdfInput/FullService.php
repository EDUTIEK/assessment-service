<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfInput;

use Edutiek\AssessmentService\EssayTask\Data\Essay;

interface FullService
{
    public function handleInput(Essay $essay): void;
}
