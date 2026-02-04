<?php

namespace Edutiek\AssessmentService\System\Spreadsheet;

class Sheet
{
    public function __construct(
        private ?string $title,
        private array $header,
        private array $rows
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
