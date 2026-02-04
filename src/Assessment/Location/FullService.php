<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Location;

use Edutiek\AssessmentService\Assessment\Data\Location;

interface FullService extends ReadService
{
    public function one(int $id): ?Location;

    /** @return Location[] */
    public function new(): Location;
    public function save(Location $location);
    /** @param string[] $titles */
    public function saveTitles(array $titles): void;
}