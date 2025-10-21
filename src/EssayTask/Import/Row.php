<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

final readonly class Row
{
    public function __construct(
        private string $id,
        private array $fields,
        private array $overwrites,
        private bool $importPossible
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getOverwrites(): array
    {
        return $this->overwrites;
    }

    public function getImportPossible(): bool
    {
        return $this->importPossible;
    }

}
