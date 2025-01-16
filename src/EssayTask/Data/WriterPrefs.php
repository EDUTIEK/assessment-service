<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class WriterPrefs implements EssayTaskEntity
{
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): void;
    public abstract function getInstructionsZoom(): float;
    public abstract function setInstructionsZoom(float $instructions_zoom): void;
    public abstract function getEditorZoom(): float;
    public abstract function setEditorZoom(float $editor_zoom): void;
    public abstract function getWordCountEnabled(): int;
    public abstract function setWordCountEnabled(int $word_count_enabled): void;
    public abstract function getWordCountCharacters(): int;
    public abstract function setWordCountCharacters(int $word_count_characters): void;
}
