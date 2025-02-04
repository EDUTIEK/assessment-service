<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class WriterPrefs implements EssayTaskEntity
{
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getInstructionsZoom(): float;
    abstract public function setInstructionsZoom(float $instructions_zoom): self;
    abstract public function getEditorZoom(): float;
    abstract public function setEditorZoom(float $editor_zoom): self;
    abstract public function getWordCountEnabled(): int;
    abstract public function setWordCountEnabled(int $word_count_enabled): self;
    abstract public function getWordCountCharacters(): int;
    abstract public function setWordCountCharacters(int $word_count_characters): self;
}
