<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

interface FullService
{
    public function import(Type $type, array $file_map, bool $overwrite_existing = false): int;
    public function type(string $type): Type;
    public function isRelevantFile(string $name): bool;
    public function typeByFiles(array $files): ?string;
}
