<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

class Set
{
    private array $entries = [];

    public function has(string $entry): bool
    {
        return isset($this->entries[$entry]);
    }

    public function add(string $entry): void
    {
        $this->entries[$entry] = true;
    }
}
