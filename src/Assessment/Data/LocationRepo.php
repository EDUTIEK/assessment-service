<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface LocationRepo
{
    public function new(): Location;
    public function one(int $id): ?Location;
    /** @return Location[] */
    public function allByAssId(int $ass_id): array;
    public function save(Location $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;

    /**
     * Get up max 100 examples of distinct location titles, sorted alphabetically
     * @return string[]
     */
    public function examples(): array;
}
