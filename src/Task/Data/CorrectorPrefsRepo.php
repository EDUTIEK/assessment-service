<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface CorrectorPrefsRepo
{
    public function new(): CorrectorPrefs;
    public function one(int $corrector_id): ?CorrectorPrefs;
    public function save(CorrectorPrefs $entity): void;
    public function delete(int $corrector_id): void;
}