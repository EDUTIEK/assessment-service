<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\EssayTask\Data\EssayImport;

interface FullService
{
    public function new(string $file_id, ?string $password, ?string $hash): EssayImport;
    public function getById(int $id): ?EssayImport;
    public function save(EssayImport $import): void;
    public function delete(EssayImport $import): void;
}
