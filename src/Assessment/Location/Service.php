<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Location;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Location\FullService;
use Edutiek\AssessmentService\Assessment\Data\Location;
use Edutiek\AssessmentService\Assessment\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->location()->allByAssId($this->ass_id);
    }

    public function new(): Location
    {
        return $this->repos->location()->new()->setAssId($this->ass_id);
    }

    public function save(Location $location) : void
    {
        $this->checkScope($location);
        $this->repos->location()->save($location);
    }

    private function checkScope(Location $location)
    {
        if ($location->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
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