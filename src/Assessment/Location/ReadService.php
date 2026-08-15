<?php

namespace Edutiek\AssessmentService\Assessment\Location;

use Edutiek\AssessmentService\Assessment\Data\Location;

interface ReadService
{
    /** @return string[] */
    public function exampleTitles(): array;

    /** @return string[] */
    public function allTitlesIndexed(): array;

    /** @return Location[] */
    public function all(): array;
}
