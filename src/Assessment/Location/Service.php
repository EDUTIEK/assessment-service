<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Location;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Location\FullService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function exampleTitles(): array
    {
        return $this->repos->location()->examples();
    }

    public function allTitles() : array
    {
        $titles = [];
        foreach($this->repos->location()->allByAssId($this->ass_id) as $location) {
            $titles[] = $location->getTitle();
        }
        sort($titles);
        return array_unique($titles);
    }

    public function saveTitles(array $titles) : void
    {
        $existing = [];
        foreach($this->repos->location()->allByAssId($this->ass_id) as $location) {
            if (!in_array($location->getTitle(), $titles)) {
                $this->repos->location()->delete($location->getId());
            } else {
              $existing[] = $location->getTitle();
            }
        }
        foreach ($titles as $title) {
            if (!in_array($title, $existing)) {
                $this->repos->location()->save($this->repos->location()->new()
                ->setAssId($this->ass_id)
                ->setTitle($title)
                );
            }
        }
    }
}