<?php

namespace Edutiek\AssessmentService\Assessment\AssessmentGrading;

use Edutiek\AssessmentService\Assessment\Data\GradeLevel;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

class Service implements FullService
{
    /**
     * @var GradeLevel[]
     */
    private ?array $grade_levels_by_id = null;
    /**
     * @var GradeLevel[]
     */
    private ?array $grade_levels = null;
    /**
     * Align with $grade_levels for a binary search for the fitting grade level
     * @var float[]
     */
     private array $min_points;

    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function recalculate(): void
    {
        foreach ($this->repos->writer()->allByAssId($this->ass_id) as $writer) {
            $level = $this->getGradLevelForPoints($writer->getFinalPoints());

            if ($level !== null && $level->getId() !== $writer->getFinalGradeLevelId()) {
                $writer->setFinalGradeLevelId($level->getId());
                $this->repos->writer()->save($writer);
            }
        }
    }

    public function getGradeLevel(?int $id): ?GradeLevel
    {
        if($id === null ) {
            return null;
        }

        $levels = $this->getGradeLevels();
        return $levels[$id] ?? null;
    }

    public function getGradLevelForPoints(?float $points): ?GradeLevel
    {
        if (!isset($points)) {
            return null;
        }
        $low = 0;
        $high = count($this->min_points) - 1;
        $best = null;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            if ($this->min_points[$mid] <= $points) {
                $best = $mid;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        } // Binary search for the next min_points

        return $best !== null ? $this->grade_levels[$best] : null;
    }

    private function getMinPoints(): array
    {
        if($this->min_points !== null) {
            return $this->min_points;
        }

        $this->grade_levels ??= $this->repos->gradeLevel()->allByAssId($this->ass_id);
        usort($this->grade_levels, fn(GradeLevel $a, GradeLevel $b) => $a->getMinPoints() <=> $b->getMinPoints()); // sort grade levels


        return $this->min_points = array_map(fn(GradeLevel $level) => $level->getMinPoints(), $this->grade_levels);
    }

    private function getGradeLevels(): array
    {
        if ($this->grade_levels_by_id === null) {
            $this->grade_levels ??= $this->repos->gradeLevel()->allByAssId($this->ass_id);

            foreach ($this->grade_levels as $level) {
                $this->grade_levels_by_id[$level->getId()] = $level;
            }
        }

        return $this->grade_levels_by_id;
    }
}
