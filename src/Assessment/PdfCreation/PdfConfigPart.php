<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

class PdfConfigPart
{
    private bool $active = false;
    private int $position = 0;

    public function __construct(
        private readonly string $component,
        private readonly string $key,
        private readonly string $type,
        private readonly string $title,
        private readonly bool $hasPageNumbers
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

    /**
     * Get the activation of the part in the pdf of an assessment
     * This is set by the PDF creation service
     */
    public function getIsActive(): bool
    {
        return $this->active;
    }

    /**
     * Set the activation of the part in the pdf of an assessment
     * This is set by the PDF creation service
     */
    public function setIsActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Set the position of the part in the pdf of an assessment
     * This is set by the PDF creation service
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Set the position of the part in the pdf of an assessment
     * This is set by the PDF creation service
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

}
