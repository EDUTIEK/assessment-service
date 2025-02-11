<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeInterface;

interface FullService
{
    function dates(?DateTimeInterface $start = null, ?DateTimeInterface $end = null) : string;
}