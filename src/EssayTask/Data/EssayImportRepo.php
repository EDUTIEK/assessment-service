<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface EssayImportRepo
{
    public function new(): EssayImport;
    public function one(int $id): ?EssayImport;
    public function delete(int $id): void;
}
