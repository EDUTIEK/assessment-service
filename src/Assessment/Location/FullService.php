<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Location;

use Edutiek\AssessmentService\Assessment\Data\Location;

interface FullService
{
    /** @return Location[] */
    public function all(): array;
    public function new(): Location;
    public function save(Location $location);

    /** @return string[] */
    public function exampleTitles(): array;

    /** @return string[] */
    public function allTitles(): array;

    /** @param string[] $titles */
    public function saveTitles(array $titles): void;
}