<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Alert;

use Edutiek\AssessmentService\Assessment\Data\Alert;

interface FullService
{
    /** @return Alert[] */
    public function all(): array;
    public function new(): Alert;
    public function one(int $id): ?Alert;
    public function create(Alert $alert);
    public function delete(Alert $alert): void;
}