<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

readonly class PdfConfigPart
{
    public function __construct(
        private string $component,
        private string $key,
        private string $type,
        private string $title,
        private bool $hasPageNumbers
    ) {
    }

    /**
     * Name of the component that implements this part, e.h. "Assessment", "Task", "EssayTask"
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Component specific key of the part, e.g. "summary_corrector1"
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Component specific type of the part, e.g. "summary"
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Title of the part that is shown in the sortable table
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Indicates that a page number should be added on the pages of this part
     */
    public function hasPageNumbers(): bool
    {
        return $this->hasPageNumbers;
    }
}
