<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Properties;

use Edutiek\AssessmentService\Assessment\Data\Properties;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }


    public function get(): Properties
    {
        return $this->repos->properties()->one($this->ass_id);
    }

    public function validate(Properties $properties): bool
    {
        return true;
    }

    public function save(Properties $properties): void
    {
        $this->repos->properties()->save($properties);
    }
}
